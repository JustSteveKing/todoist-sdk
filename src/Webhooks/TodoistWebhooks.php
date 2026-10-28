<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Webhooks;

use JsonException;
use JustSteveKing\SdkToolbox\Errors\InvalidSignatureException;
use JustSteveKing\SdkToolbox\Webhooks\HmacWebhook;
use JustSteveKing\SdkToolbox\Webhooks\RawBody;
use JustSteveKing\SdkToolbox\Webhooks\WebhookVerifier;
use Psr\Http\Message\RequestInterface;
use SensitiveParameter;

/**
 * Checks and reads Todoist's webhook deliveries.
 *
 * Todoist signs each one with `X-Todoist-Hmac-SHA256`: an HMAC-SHA256 of
 * the raw body, keyed with your app's client secret, base64-encoded. The
 * comparison is constant-time, and the body is read exactly as it
 * arrived, so verify the PSR-7 request before anything parses it.
 *
 *     $event = (new TodoistWebhooks($clientSecret))->receive($request);
 *
 * There's no timestamp in the signature, so it can't stop a replay of a
 * genuine delivery on its own: deduplicate on `deliveryId`. Answer 200
 * once you have it; anything else is a failed delivery, and Todoist
 * tries again in 15 minutes, up to three times.
 */
final readonly class TodoistWebhooks implements WebhookVerifier
{
    public const string SIGNATURE = 'X-Todoist-Hmac-SHA256';

    public const string DELIVERY = 'X-Todoist-Delivery-ID';

    private HmacWebhook $hmac;

    public function __construct(#[SensitiveParameter] string $clientSecret)
    {
        $this->hmac = new HmacWebhook($clientSecret, self::SIGNATURE, algorithm: 'sha256', encoding: 'base64');
    }

    /**
     * @throws InvalidSignatureException when the signature is missing or wrong
     */
    public function verify(RequestInterface $request): void
    {
        $this->hmac->verify($request);
    }

    /**
     * Verifies the delivery, then reads it.
     *
     * @throws InvalidSignatureException when the signature is missing or wrong, or the signed body isn't an event
     */
    public function receive(RequestInterface $request): WebhookEvent
    {
        $this->verify($request);

        try {
            $payload = json_decode(RawBody::of($request), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidSignatureException('The webhook is signed, but its body isn\'t JSON.', previous: $e);
        }

        $name = is_array($payload) ? ($payload['event_name'] ?? null) : null;
        $userId = is_array($payload) ? ($payload['user_id'] ?? null) : null;

        if (! is_array($payload) || ! is_string($name) || (! is_string($userId) && ! is_int($userId))) {
            throw new InvalidSignatureException('The webhook is signed, but its body isn\'t a Todoist event.');
        }

        $delivery = trim($request->getHeaderLine(self::DELIVERY));

        return new WebhookEvent(
            name: $name,
            userId: (string) $userId,
            data: self::object($payload['event_data'] ?? null),
            initiator: self::initiator($payload['initiator'] ?? null),
            triggeredAt: is_string($payload['triggered_at'] ?? null) ? $payload['triggered_at'] : null,
            version: is_string($payload['version'] ?? null) || is_int($payload['version'] ?? null) ? (string) $payload['version'] : null,
            deliveryId: $delivery === '' ? null : $delivery,
            extra: self::object($payload['event_data_extra'] ?? null),
        );
    }

    /**
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['secret' => '[redacted]'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function object(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $object = [];

        foreach ($value as $key => $item) {
            $object[(string) $key] = $item;
        }

        return $object;
    }

    private static function initiator(mixed $value): ?Initiator
    {
        if (! is_array($value) || (! is_string($value['id'] ?? null) && ! is_int($value['id'] ?? null))) {
            return null;
        }

        return new Initiator(
            id: (string) $value['id'],
            email: is_string($value['email'] ?? null) ? $value['email'] : null,
            fullName: is_string($value['full_name'] ?? null) ? $value['full_name'] : null,
            imageId: is_string($value['image_id'] ?? null) ? $value['image_id'] : null,
            isPremium: is_bool($value['is_premium'] ?? null) ? $value['is_premium'] : null,
        );
    }
}
