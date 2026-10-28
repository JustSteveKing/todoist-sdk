<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\OAuth;

use JustSteveKing\SdkToolbox\Auth\OAuth\AccessToken;
use JustSteveKing\SdkToolbox\Auth\OAuth\OAuthClient;
use JustSteveKing\SdkToolbox\Auth\OAuth\ProcessLock;
use JustSteveKing\SdkToolbox\Auth\OAuth\RefreshLock;
use JustSteveKing\SdkToolbox\Auth\OAuth\TokenStore;
use JustSteveKing\SdkToolbox\Auth\Refreshable;
use JustSteveKing\SdkToolbox\Errors\OAuthException;
use Psr\Http\Message\RequestInterface;

/**
 * Keeps a user's Todoist token fresh, the way Todoist's refresh tokens
 * need.
 *
 * Todoist rotates the refresh token on every refresh. If the same one is
 * sent again within 60 seconds, it treats that as a network retry: it
 * answers with the same new access token, but no refresh token, because
 * it can't reissue one. Sent again after that, it reads as a replay, and
 * Todoist revokes every token the user has given the app.
 *
 * sdk-toolbox's RefreshingToken keeps the old refresh token when a
 * response has none, which is right for servers that don't rotate, and
 * wrong here: the old one is spent, and using it an hour later costs the
 * user every session. So a response without a refresh token is stored
 * without one. When that token expires the user authorises again, which
 * a lost refresh response always meant.
 *
 * A token the API rejects is refreshed once and the request sent again,
 * as with any toolbox OAuth authenticator. Every refresh runs inside the
 * lock, and reads the store again first, so ten workers with the same
 * expired token refresh it once.
 */
final readonly class RefreshingTodoistToken implements Refreshable
{
    public function __construct(
        private OAuthClient $client,
        private string $key,
        private TokenStore $store,
        private RefreshLock $lock = new ProcessLock,
        private int $leeway = 30,
    ) {}

    public function authenticate(RequestInterface $request): RequestInterface
    {
        $token = $this->store->get($this->key);

        if ($token === null || $token->expired($this->client->clock, $this->leeway)) {
            $token = $this->renew($token?->token);
        }

        return $request->withHeader('Authorization', "Bearer {$token->token}");
    }

    public function refresh(RequestInterface $rejected): void
    {
        $header = $rejected->getHeaderLine('Authorization');

        $this->renew(str_starts_with($header, 'Bearer ') ? substr($header, 7) : null);
    }

    public function sensitive(): array
    {
        return ['headers' => [], 'query' => []];
    }

    private function renew(?string $rejected): AccessToken
    {
        return $this->lock->synchronized($this->key, function () use ($rejected): AccessToken {
            $stored = $this->store->get($this->key);

            if ($stored !== null && $stored->token !== $rejected && ! $stored->expired($this->client->clock, $this->leeway)) {
                return $stored;
            }

            if ($stored?->refreshToken === null) {
                throw new OAuthException('no_refresh_token', 'There is no refresh token for this key; the user has to authorise again.');
            }

            $fresh = $this->client->requestToken(
                ['grant_type' => 'refresh_token', 'refresh_token' => $stored->refreshToken],
                $stored->scopes,
            );

            $this->store->put($this->key, $fresh);

            return $fresh;
        });
    }
}
