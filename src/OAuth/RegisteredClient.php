<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\OAuth;

use JustSteveKing\SdkToolbox\Errors\OAuthException;
use SensitiveParameter;

/**
 * What Todoist issued for a runtime registration. Its client ID starts
 * `tdd_`, and its secret, if it has one, never expires: store it as you
 * would any other secret.
 */
final readonly class RegisteredClient
{
    /**
     * @param  list<string>  $redirectUris
     * @param  list<string>  $grantTypes
     */
    public function __construct(
        public string $clientId,
        #[SensitiveParameter]
        private ?string $clientSecret,
        public array $redirectUris,
        public ?string $clientName,
        public ?string $scope,
        public array $grantTypes,
        public string $tokenEndpointAuthMethod,
        public ?int $clientIdIssuedAt,
    ) {}

    /**
     * @param  array<array-key, mixed>  $data
     *
     * @throws OAuthException when the response has no client ID
     */
    public static function fromResponse(array $data): self
    {
        if (! is_string($data['client_id'] ?? null) || $data['client_id'] === '') {
            throw new OAuthException('invalid_response', 'The registration response has no client_id.');
        }

        $strings = static fn (mixed $value): array => is_array($value) ? array_values(array_filter($value, is_string(...))) : [];

        return new self(
            clientId: $data['client_id'],
            clientSecret: is_string($data['client_secret'] ?? null) ? $data['client_secret'] : null,
            redirectUris: $strings($data['redirect_uris'] ?? null),
            clientName: is_string($data['client_name'] ?? null) ? $data['client_name'] : null,
            scope: is_string($data['scope'] ?? null) ? $data['scope'] : null,
            grantTypes: $strings($data['grant_types'] ?? null),
            tokenEndpointAuthMethod: is_string($data['token_endpoint_auth_method'] ?? null) ? $data['token_endpoint_auth_method'] : 'client_secret_post',
            clientIdIssuedAt: is_int($data['client_id_issued_at'] ?? null) ? $data['client_id_issued_at'] : null,
        );
    }

    public function clientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function isPublic(): bool
    {
        return $this->clientSecret === null;
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret === null ? null : '[redacted]',
            'redirectUris' => $this->redirectUris,
            'clientName' => $this->clientName,
            'scope' => $this->scope,
            'grantTypes' => $this->grantTypes,
            'tokenEndpointAuthMethod' => $this->tokenEndpointAuthMethod,
            'clientIdIssuedAt' => $this->clientIdIssuedAt,
        ];
    }
}
