<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\OAuth;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use JsonException;
use JustSteveKing\SdkToolbox\Auth\OAuth\AccessToken;
use JustSteveKing\SdkToolbox\Auth\OAuth\AuthorizationCode;
use JustSteveKing\SdkToolbox\Auth\OAuth\AuthorizationRequest;
use JustSteveKing\SdkToolbox\Auth\OAuth\ClientAuthentication;
use JustSteveKing\SdkToolbox\Auth\OAuth\ClientRegistration as Registration;
use JustSteveKing\SdkToolbox\Auth\OAuth\OAuthClient;
use JustSteveKing\SdkToolbox\Auth\OAuth\ProcessLock;
use JustSteveKing\SdkToolbox\Auth\OAuth\RefreshingToken;
use JustSteveKing\SdkToolbox\Auth\OAuth\RefreshLock;
use JustSteveKing\SdkToolbox\Auth\OAuth\RegisteredClient;
use JustSteveKing\SdkToolbox\Auth\OAuth\TokenStore;
use JustSteveKing\SdkToolbox\Errors\InvalidRequestException;
use JustSteveKing\SdkToolbox\Errors\OAuthException;
use JustSteveKing\SdkToolbox\Errors\TransportException;
use JustSteveKing\SdkToolbox\Http\QueryString;
use JustSteveKing\SdkToolbox\Redaction\Redactor;
use JustSteveKing\SdkToolbox\Time\SystemClock;
use Psr\Clock\ClockInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SensitiveParameter;

/**
 * A Todoist app's side of OAuth: sending a user to authorise it, turning
 * the code that comes back into a token, keeping that token fresh, and
 * revoking it.
 *
 *     $oauth = new TodoistOAuth($clientId, $clientSecret);
 *
 *     $started = $oauth->start('https://app.test/callback', Scope::DataReadWrite);
 *     $session->put('todoist_oauth', $started);
 *     // redirect to $started->url
 *
 *     $token = $oauth->exchange('https://app.test/callback', $session->pull('todoist_oauth'), $code, $state);
 *     $store->put("user:{$id}", $token);
 *
 *     $todoist = new Todoist($oauth->refreshing("user:{$id}", $store));
 *
 * PKCE is sent on every flow, and `state` is checked in constant time,
 * both by sdk-toolbox. A client with no secret is a public client: Todoist
 * then relies on PKCE alone. Its endpoints are fixed and HTTPS.
 */
final readonly class TodoistOAuth
{
    public const string AUTHORIZE = 'https://app.todoist.com/oauth/authorize';

    public const string TOKEN = 'https://api.todoist.com/oauth/access_token';

    public const string REVOKE = 'https://api.todoist.com/api/v1/revoke';

    public const string REGISTER = 'https://api.todoist.com/oauth/register';

    private OAuthClient $client;

    private ClientInterface $http;

    private RequestFactoryInterface $requests;

    private StreamFactoryInterface $streams;

    /**
     * @param  string|null  $clientSecret  null for a public client: a CLI, a desktop app, an MCP server
     */
    public function __construct(
        public string $clientId,
        #[SensitiveParameter]
        private ?string $clientSecret = null,
        ?ClientInterface $http = null,
        ?RequestFactoryInterface $requests = null,
        ?StreamFactoryInterface $streams = null,
        ClockInterface $clock = new SystemClock,
    ) {
        $this->http = $http ?? Psr18ClientDiscovery::find();
        $this->requests = $requests ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streams = $streams ?? Psr17FactoryDiscovery::findStreamFactory();

        // Todoist's default is client_secret_post: the secret in the body.
        $this->client = new OAuthClient(
            tokenEndpoint: self::TOKEN,
            clientId: $clientId,
            clientSecret: $clientSecret,
            authorizationEndpoint: self::AUTHORIZE,
            authentication: ClientAuthentication::Post,
            http: $this->http,
            requests: $this->requests,
            streams: $this->streams,
            clock: $clock,
            // Todoist reads the scope parameter as a comma-separated list.
            scopeSeparator: ',',
        );
    }

    /**
     * Registers a client at runtime (RFC 7591), for apps that can't use
     * Todoist's App Management Console: multi-tenant integrations, MCP
     * servers, agents. No authentication; Todoist rate limits it.
     *
     * @throws OAuthException `invalid_client_metadata` or `invalid_scope`, or another refusal
     */
    public static function register(
        ClientRegistration $registration,
        ?ClientInterface $http = null,
        ?RequestFactoryInterface $requests = null,
        ?StreamFactoryInterface $streams = null,
    ): RegisteredClient {
        return (new Registration(self::REGISTER, $http, $requests, $streams))->register($registration->toMetadata());
    }

    /**
     * A client for what came back from register().
     */
    public static function fromRegistration(
        RegisteredClient $client,
        ?ClientInterface $http = null,
        ?RequestFactoryInterface $requests = null,
        ?StreamFactoryInterface $streams = null,
        ClockInterface $clock = new SystemClock,
    ): self {
        return new self($client->clientId, $client->clientSecret(), $http, $requests, $streams, $clock);
    }

    /**
     * The URL to send the user to, with the state and PKCE verifier to
     * keep until they come back. Keep it in their session.
     */
    public function start(string $redirectUri, Scope ...$scopes): AuthorizationRequest
    {
        return (new AuthorizationCode($this->client, $redirectUri))->start(array_map(static fn (Scope $scope): string => $scope->value, array_values($scopes)));
    }

    /**
     * Turns the code the user came back with into a token, once the state
     * they came back with matches the one sent.
     *
     * @throws OAuthException `invalid_state`, or Todoist's refusal, such as `bad_authorization_code`
     */
    public function exchange(string $redirectUri, AuthorizationRequest $started, string $code, string $state): AccessToken
    {
        return (new AuthorizationCode($this->client, $redirectUri))->exchange($started, $code, $state);
    }

    /**
     * An authenticator for one user's stored token, kept fresh.
     *
     * Todoist rotates refresh tokens. A refresh retried within 60 seconds
     * gets the same access token back without a refresh token, because
     * the old one is spent; sent again later, that reads as a replay and
     * Todoist revokes every token the user gave the app. `rotating: true`
     * makes sure it's never sent again: the user authorises again instead.
     */
    public function refreshing(string $key, TokenStore $store, RefreshLock $lock = new ProcessLock): RefreshingToken
    {
        return new RefreshingToken($this->client, $key, $store, $lock, rotating: true);
    }

    /**
     * Revokes an access token (RFC 7009). Todoist wants the app's own
     * credentials in HTTP Basic, so a public client can't.
     *
     * @throws OAuthException when Todoist refuses
     */
    public function revoke(#[SensitiveParameter] string $accessToken): void
    {
        if ($this->clientSecret === null) {
            throw new InvalidRequestException('Revoking a token needs the client secret; a public client can\'t.');
        }

        $request = $this->requests->createRequest('POST', self::REVOKE)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            // Form-encoded before Basic-encoded, as RFC 6749 section 2.3.1 says.
            ->withHeader('Authorization', 'Basic '.base64_encode(urlencode($this->clientId).':'.urlencode($this->clientSecret)))
            ->withBody($this->streams->createStream(QueryString::build(['token' => $accessToken, 'token_type_hint' => 'access_token'])));

        self::call($this->http, $request);
    }

    /**
     * The toolbox client underneath, for anything this class doesn't wrap.
     */
    public function client(): OAuthClient
    {
        return $this->client;
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return ['clientId' => $this->clientId, 'clientSecret' => $this->clientSecret === null ? null : '[redacted]'];
    }

    /**
     * Sends the revocation, and reads either error shape Todoist uses
     * there: RFC 6749's `error`, or its API's `error_tag`.
     *
     * @return array<array-key, mixed>
     */
    private static function call(ClientInterface $http, RequestInterface $request): array
    {
        try {
            $response = $http->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw TransportException::from($e, $request, new Redactor);
        }

        $status = $response->getStatusCode();

        try {
            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $data = null;
        }

        if ($status < 400) {
            return is_array($data) ? $data : [];
        }

        if (! is_array($data)) {
            throw new OAuthException('invalid_response', "Todoist answered {$status} without JSON.", status: $status);
        }

        $tag = $data['error_tag'] ?? null;
        $error = $data['error'] ?? null;

        throw new OAuthException(
            error: is_string($tag) ? strtolower($tag) : (is_string($error) ? $error : 'invalid_response'),
            description: is_string($data['error_description'] ?? null) ? $data['error_description'] : (is_string($tag) && is_string($error) ? $error : null),
            status: $status,
        );
    }
}
