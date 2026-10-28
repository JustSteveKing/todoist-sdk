<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\OAuth;

/**
 * A client to register at runtime (RFC 7591).
 *
 * Redirect URIs must be HTTPS (localhost is allowed for testing), up to
 * ten. A public client gets no secret and authenticates with PKCE alone.
 * Refresh tokens are on unless you say otherwise.
 */
final readonly class ClientRegistration
{
    /**
     * @param  list<string>  $redirectUris
     * @param  list<Scope>  $scopes  Todoist defaults to data:read_write when none are given
     */
    public function __construct(
        public array $redirectUris,
        public ?string $clientName = null,
        public ?string $clientUri = null,
        public ?string $logoUri = null,
        public array $scopes = [],
        public bool $refreshTokens = true,
        public bool $public = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'redirect_uris' => $this->redirectUris,
            'client_name' => $this->clientName,
            'client_uri' => $this->clientUri,
            'logo_uri' => $this->logoUri,
            // Space-separated here, as RFC 7591 says, unlike the
            // authorisation request's commas.
            'scope' => $this->scopes === [] ? null : implode(' ', array_map(static fn (Scope $scope): string => $scope->value, $this->scopes)),
            'grant_types' => $this->refreshTokens ? ['authorization_code', 'refresh_token'] : ['authorization_code'],
            'response_types' => ['code'],
            'token_endpoint_auth_method' => $this->public ? 'none' : 'client_secret_post',
        ], static fn (mixed $value): bool => $value !== null);
    }
}
