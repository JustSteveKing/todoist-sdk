<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\OAuth;

use JustSteveKing\SdkToolbox\Auth\OAuth\ClientMetadata;

/**
 * A client to register at runtime (RFC 7591), with Todoist's defaults:
 * refresh tokens on, and the secret sent in the body. It becomes
 * sdk-toolbox's ClientMetadata, which does the registering.
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

    public function toMetadata(): ClientMetadata
    {
        return new ClientMetadata(
            redirectUris: $this->redirectUris,
            clientName: $this->clientName,
            clientUri: $this->clientUri,
            logoUri: $this->logoUri,
            // Space-separated, as RFC 7591 defines `scope`, unlike the
            // authorisation request's commas.
            scopes: array_map(static fn (Scope $scope): string => $scope->value, $this->scopes),
            grantTypes: $this->refreshTokens ? ['authorization_code', 'refresh_token'] : ['authorization_code'],
            tokenEndpointAuthMethod: $this->public ? 'none' : 'client_secret_post',
        );
    }
}
