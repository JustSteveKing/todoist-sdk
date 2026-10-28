<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Auth\OAuth\AccessToken;
use JustSteveKing\SdkToolbox\Auth\OAuth\InMemoryTokenStore;
use JustSteveKing\SdkToolbox\Errors\InvalidRequestException;
use JustSteveKing\SdkToolbox\Errors\OAuthException;
use JustSteveKing\SdkToolbox\Retries\RetryPolicy;
use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeResponse;
use JustSteveKing\SdkToolbox\Testing\FakeTime;
use JustSteveKing\TodoistSdk\OAuth\ClientRegistration;
use JustSteveKing\TodoistSdk\OAuth\Scope;
use JustSteveKing\TodoistSdk\OAuth\TodoistOAuth;
use JustSteveKing\TodoistSdk\Todoist;
use Psr\Http\Message\RequestInterface;

/**
 * @return array<string, mixed>
 */
function form(?RequestInterface $request): array
{
    parse_str((string) $request?->getBody(), $fields);

    return $fields;
}

/**
 * The token response Todoist documents for an app with refresh tokens.
 *
 * @param  array<string, mixed>  $overrides
 */
function tokenResponse(array $overrides = []): Psr\Http\Message\ResponseInterface
{
    return FakeResponse::json([
        'access_token' => 'access-1',
        'token_type' => 'Bearer',
        'expires_in' => 3600,
        'refresh_token' => 'refresh-1',
        'scope' => 'data:read_write',
        ...$overrides,
    ]);
}

it('sends the user to Todoist with comma-separated scopes, state and PKCE', function (): void {
    $started = (new TodoistOAuth('client-id', 'secret', new FakeClient))
        ->start('https://app.test/callback', Scope::DataReadWrite, Scope::DataDelete);

    $url = parse_url($started->url);
    parse_str($url['query'] ?? '', $query);

    expect("{$url['scheme']}://{$url['host']}{$url['path']}")->toBe(TodoistOAuth::AUTHORIZE)
        ->and($query)->toMatchArray([
            'client_id' => 'client-id',
            'redirect_uri' => 'https://app.test/callback',
            'scope' => 'data:read_write,data:delete',
            'response_type' => 'code',
            'state' => $started->state,
            'code_challenge' => $started->pkce->challenge(),
            'code_challenge_method' => 'S256',
        ]);
});

it('exchanges the code, with the secret in the body as Todoist expects by default', function (): void {
    $http = new FakeClient(tokenResponse());
    $oauth = new TodoistOAuth('client-id', 'secret', $http, clock: new FakeTime('2026-11-16 09:00:00 UTC'));
    $started = $oauth->start('https://app.test/callback', Scope::DataReadWrite);

    $token = $oauth->exchange('https://app.test/callback', $started, 'the-code', $started->state);

    expect($token->token)->toBe('access-1')
        ->and($token->refreshToken)->toBe('refresh-1')
        ->and($token->expiresAt?->format('H:i'))->toBe('10:00')
        ->and((string) $http->last()?->getUri())->toBe(TodoistOAuth::TOKEN)
        ->and($http->last()?->hasHeader('Authorization'))->toBeFalse()
        ->and(form($http->last()))->toBe([
            'grant_type' => 'authorization_code',
            'code' => 'the-code',
            'redirect_uri' => 'https://app.test/callback',
            'code_verifier' => $started->pkce->verifier,
            'client_id' => 'client-id',
            'client_secret' => 'secret',
        ]);
});

it('sends no secret for a public client, which relies on PKCE', function (): void {
    $http = new FakeClient(tokenResponse());
    $oauth = new TodoistOAuth('https://app.test/oauth/client.json', http: $http);
    $started = $oauth->start('https://app.test/callback');

    $oauth->exchange('https://app.test/callback', $started, 'the-code', $started->state);

    expect(form($http->last()))->not->toHaveKey('client_secret')
        ->and(form($http->last())['client_id'])->toBe('https://app.test/oauth/client.json');
});

it('refuses a state that does not match, before sending anything', function (): void {
    $http = new FakeClient;
    $oauth = new TodoistOAuth('client-id', 'secret', $http);

    expect(fn () => $oauth->exchange('https://app.test/callback', $oauth->start('https://app.test/callback'), 'code', 'forged'))
        ->toThrow(fn (OAuthException $e) => expect($e->error)->toBe('invalid_state'));
    $http->assertNothingSent();
});

it('reports Todoist\'s refusal of a bad code', function (): void {
    $http = new FakeClient(FakeResponse::json(['error' => 'bad_authorization_code'], 400));
    $oauth = new TodoistOAuth('client-id', 'secret', $http);
    $started = $oauth->start('https://app.test/callback');

    expect(fn () => $oauth->exchange('https://app.test/callback', $started, 'stale', $started->state))
        ->toThrow(fn (OAuthException $e) => expect($e->error)->toBe('bad_authorization_code'));
});

it('refreshes an expired token, and keeps the rotated refresh token', function (): void {
    $time = new FakeTime('2026-11-16 09:00:00 UTC');
    $tokens = new FakeClient(tokenResponse(['access_token' => 'access-2', 'refresh_token' => 'refresh-2']));
    $api = new FakeClient(FakeResponse::json(taskJson()));
    $store = new InMemoryTokenStore;
    $store->put('user:1', new AccessToken('access-1', $time->now()->modify('-1 minute'), 'refresh-1'));
    $oauth = new TodoistOAuth('client-id', 'secret', $tokens, clock: $time);

    (new Todoist($oauth->refreshing('user:1', $store), $api, retry: RetryPolicy::none(), clock: $time, sleeper: $time))->tasks()->get('t1');

    expect(form($tokens->last()))->toMatchArray(['grant_type' => 'refresh_token', 'refresh_token' => 'refresh-1'])
        ->and($api->last()?->getHeaderLine('Authorization'))->toBe('Bearer access-2')
        ->and($store->get('user:1')?->refreshToken)->toBe('refresh-2');
});

it('never sends a spent refresh token again after Todoist\'s grace-window reply', function (): void {
    $time = new FakeTime('2026-11-16 09:00:00 UTC');
    // Inside Todoist's 60-second grace window a repeated refresh gets the
    // same access token back, without a refresh token.
    $tokens = new FakeClient(FakeResponse::json(['access_token' => 'access-2', 'token_type' => 'Bearer', 'expires_in' => 3600]));
    $api = new FakeClient(FakeResponse::json(taskJson()));
    $store = new InMemoryTokenStore;
    $store->put('user:1', new AccessToken('access-1', $time->now()->modify('-1 minute'), 'refresh-1'));
    $todoist = new Todoist((new TodoistOAuth('client-id', 'secret', $tokens, clock: $time))->refreshing('user:1', $store), $api, retry: RetryPolicy::none(), clock: $time, sleeper: $time);

    $todoist->tasks()->get('t1');

    expect($store->get('user:1')?->refreshToken)->toBeNull();

    $time->advance(3600);

    // Sending refresh-1 now would read as a replay, and Todoist would
    // revoke every token the user has given the app.
    expect(fn () => $todoist->tasks()->get('t1'))
        ->toThrow(fn (OAuthException $e) => expect($e->error)->toBe('no_refresh_token'));
    $tokens->assertSentCount(1);
});

it('refreshes once and retries when Todoist rejects the token', function (): void {
    $time = new FakeTime('2026-11-16 09:00:00 UTC');
    $tokens = new FakeClient(tokenResponse(['access_token' => 'access-2', 'refresh_token' => 'refresh-2']));
    $api = new FakeClient(
        FakeResponse::json(['error' => 'Unauthorized', 'error_code' => 477, 'error_extra' => ['retry_after' => 3, 'event_id' => 'e'], 'error_tag' => 'UNAUTHORIZED', 'http_code' => 401], 401, [
            'Retry-After' => '3',
            'WWW-Authenticate' => 'Bearer error="invalid_token", error_description="Unauthorized", resource_metadata="https://api.todoist.com/.well-known/oauth-protected-resource"',
        ]),
        FakeResponse::json(taskJson()),
    );
    $store = new InMemoryTokenStore;
    $store->put('user:1', new AccessToken('access-1', $time->now()->modify('+30 minutes'), 'refresh-1'));
    $oauth = new TodoistOAuth('client-id', 'secret', $tokens, clock: $time);

    (new Todoist($oauth->refreshing('user:1', $store), $api, retry: RetryPolicy::none(), clock: $time, sleeper: $time))->tasks()->get('t1');

    expect(array_map(fn (RequestInterface $r): string => $r->getHeaderLine('Authorization'), $api->sent()))
        ->toBe(['Bearer access-1', 'Bearer access-2'])
        ->and($time->sleeps())->toBe([]);
});

it('revokes a token with the app\'s credentials in Basic, form-encoded first', function (): void {
    $http = new FakeClient(FakeResponse::json([]));

    (new TodoistOAuth('client-id', 'se+cret', $http))->revoke('access-1');

    expect((string) $http->last()?->getUri())->toBe(TodoistOAuth::REVOKE)
        ->and($http->last()?->getHeaderLine('Authorization'))->toBe('Basic '.base64_encode('client-id:se%2Bcret'))
        ->and(form($http->last()))->toBe(['token' => 'access-1', 'token_type_hint' => 'access_token']);
});

it('reads Todoist\'s own error shape from revoke', function (): void {
    // As the endpoint answered a probe with made-up credentials on 2026-10-28.
    $http = new FakeClient(FakeResponse::json(['error' => 'Forbidden', 'error_code' => 1, 'error_extra' => ['event_id' => 'e', 'retry_after' => 4], 'error_tag' => 'FORBIDDEN', 'http_code' => 403], 403));

    expect(fn () => (new TodoistOAuth('client-id', 'secret', $http))->revoke('access-1'))
        ->toThrow(fn (OAuthException $e) => expect([$e->error, $e->description, $e->status])->toBe(['forbidden', 'Forbidden', 403]));
});

it('cannot revoke as a public client', function (): void {
    $http = new FakeClient;

    expect(fn () => (new TodoistOAuth('client-id', http: $http))->revoke('access-1'))->toThrow(InvalidRequestException::class);
    $http->assertNothingSent();
});

it('registers a client at runtime, and keeps its secret out of dumps', function (): void {
    $http = new FakeClient(FakeResponse::json([
        'client_id' => 'tdd_0123456789abcdef0123456789abcdef',
        'client_secret' => 'issued-secret',
        'client_name' => 'My App',
        'redirect_uris' => ['https://example.com/callback'],
        'scope' => 'data:read_write',
        'grant_types' => ['authorization_code', 'refresh_token'],
        'response_types' => ['code'],
        'token_endpoint_auth_method' => 'client_secret_post',
        'client_id_issued_at' => 1776865756,
        'client_secret_expires_at' => 0,
    ], 201));

    $client = TodoistOAuth::register(new ClientRegistration(['https://example.com/callback'], 'My App', scopes: [Scope::DataRead, Scope::DataDelete]), $http);

    expect((string) $http->last()?->getUri())->toBe(TodoistOAuth::REGISTER)
        ->and(json_decode((string) $http->last()?->getBody(), true))->toBe([
            'redirect_uris' => ['https://example.com/callback'],
            'client_name' => 'My App',
            'scope' => 'data:read data:delete',
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
            'token_endpoint_auth_method' => 'client_secret_post',
        ])
        ->and($client->clientId)->toBe('tdd_0123456789abcdef0123456789abcdef')
        ->and($client->clientSecret())->toBe('issued-secret')
        ->and(print_r($client, true))->not->toContain('issued-secret')
        ->and(print_r(TodoistOAuth::fromRegistration($client, new FakeClient), true))->not->toContain('issued-secret');
});

it('registers a public client with no secret', function (): void {
    $http = new FakeClient(FakeResponse::json(['client_id' => 'tdd_x', 'token_endpoint_auth_method' => 'none'], 201));

    $client = TodoistOAuth::register(new ClientRegistration(['http://localhost:8080/callback'], public: true, refreshTokens: false), $http);

    expect($client->isPublic())->toBeTrue()
        ->and(json_decode((string) $http->last()?->getBody(), true))->toMatchArray([
            'grant_types' => ['authorization_code'],
            'token_endpoint_auth_method' => 'none',
        ]);
});

it('reports a rejected registration', function (): void {
    $http = new FakeClient(FakeResponse::json(['error' => 'invalid_client_metadata'], 400));

    expect(fn () => TodoistOAuth::register(new ClientRegistration(['http://example.com']), $http))
        ->toThrow(fn (OAuthException $e) => expect($e->error)->toBe('invalid_client_metadata'));
});
