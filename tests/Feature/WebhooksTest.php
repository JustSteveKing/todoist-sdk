<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Errors\InvalidSignatureException;
use JustSteveKing\TodoistSdk\Webhooks\Event;
use JustSteveKing\TodoistSdk\Webhooks\TodoistWebhooks;
use Nyholm\Psr7\Request;

/**
 * A delivery as Todoist sends one. Todoist's docs show a signature but
 * not the secret behind it, so the signature here is worked out the way
 * they describe: HMAC-SHA256 of the raw body, keyed with the client
 * secret, base64-encoded.
 *
 * @param  array<string, string>  $headers
 */
function delivery(string $body, ?string $secret = 'client-secret', array $headers = []): Request
{
    $signature = $secret === null ? [] : ['X-Todoist-Hmac-SHA256' => base64_encode(hash_hmac('sha256', $body, $secret, true))];

    return new Request('POST', 'https://app.test/webhooks/todoist', [
        'Content-Type' => 'application/json',
        'User-Agent' => 'Todoist-Webhooks',
        'X-Todoist-Delivery-ID' => 'delivery-1',
        ...$signature,
        ...$headers,
    ], $body);
}

function documentedDelivery(): string
{
    return (string) file_get_contents(__DIR__.'/../Fixtures/webhook-item-added.json');
}

it('reads Todoist\'s documented example once its signature checks out', function (): void {
    $event = (new TodoistWebhooks('client-secret'))->receive(delivery(documentedDelivery()));

    expect($event->event())->toBe(Event::TaskAdded)
        ->and($event->is(Event::TaskAdded))->toBeTrue()
        ->and($event->userId)->toBe('2671355')
        ->and($event->objectId())->toBe('6XR4GqQQCW6Gv9h4')
        ->and($event->data['content'])->toBe('Buy Milk')
        ->and($event->initiator?->fullName)->toBe('Alice')
        ->and($event->initiator?->isPremium)->toBeTrue()
        ->and($event->triggeredAt)->toBe('2025-02-10T10:39:38.000000Z')
        ->and($event->version)->toBe('10')
        ->and($event->deliveryId)->toBe('delivery-1')
        ->and($event->extra)->toBe([]);
});

it('leaves the body readable for whatever handles it next', function (): void {
    $request = delivery(documentedDelivery());

    (new TodoistWebhooks('client-secret'))->verify($request);

    expect((string) $request->getBody())->toBe(documentedDelivery());
});

it('refuses a delivery signed with another secret', function (): void {
    expect(fn () => (new TodoistWebhooks('client-secret'))->receive(delivery(documentedDelivery(), 'someone-else')))
        ->toThrow(InvalidSignatureException::class, 'doesn\'t match');
});

it('refuses a delivery with no signature', function (): void {
    expect(fn () => (new TodoistWebhooks('client-secret'))->receive(delivery(documentedDelivery(), null)))
        ->toThrow(InvalidSignatureException::class, 'no X-Todoist-Hmac-SHA256 signature');
});

it('refuses a body changed after signing', function (): void {
    $request = delivery(documentedDelivery());
    $tampered = $request->withBody(Nyholm\Psr7\Stream::create(str_replace('Buy Milk', 'Buy Gold', documentedDelivery())));

    expect(fn () => (new TodoistWebhooks('client-secret'))->receive($tampered))->toThrow(InvalidSignatureException::class);
});

it('refuses a hex signature, since Todoist sends base64', function (): void {
    $hex = hash_hmac('sha256', documentedDelivery(), 'client-secret');

    expect(fn () => (new TodoistWebhooks('client-secret'))->receive(delivery(documentedDelivery(), null, ['X-Todoist-Hmac-SHA256' => $hex])))
        ->toThrow(InvalidSignatureException::class);
});

it('refuses a signed body that is not a Todoist event', function (string $body): void {
    expect(fn () => (new TodoistWebhooks('client-secret'))->receive(delivery($body)))->toThrow(InvalidSignatureException::class);
})->with(['not JSON' => 'hello', 'not an object' => '"hello"', 'no event name' => '{"user_id":"1"}']);

it('keeps an event this SDK does not know yet, rather than failing', function (): void {
    $event = (new TodoistWebhooks('client-secret'))->receive(delivery('{"event_name":"workspace:added","user_id":"1","event_data":{"id":"w1"}}'));

    expect($event->event())->toBeNull()
        ->and($event->name)->toBe('workspace:added')
        ->and($event->objectId())->toBe('w1')
        ->and($event->initiator)->toBeNull();
});

it('carries the extra data some events send', function (): void {
    $body = json_encode([
        'event_name' => 'item:updated',
        'user_id' => '1',
        'event_data' => ['id' => 't1', 'content' => 'New'],
        'event_data_extra' => ['old_item' => ['id' => 't1', 'content' => 'Old'], 'update_intent' => 'item_updated'],
    ], JSON_THROW_ON_ERROR);

    $event = (new TodoistWebhooks('client-secret'))->receive(delivery($body));

    expect($event->extra['update_intent'])->toBe('item_updated');
});

it('keeps the secret out of dumps', function (): void {
    expect(print_r(new TodoistWebhooks('client-secret'), true))->not->toContain('client-secret');
});
