<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Webhooks;

/**
 * One verified webhook delivery.
 *
 * `data` is the changed object as Todoist sent it, decoded but not mapped
 * to a model. Its shape follows the webhook version set in the App
 * Management Console, not API v1: a task in Todoist's documented example
 * has `checked` and `url` but no `duration` or `updated_at`. Todoist also
 * says webhooks can arrive late, out of order or not at all, so treat one
 * as a nudge and fetch the object if you need its current state:
 *
 *     if ($event->is(Event::TaskCompleted)) {
 *         $task = $todoist->tasks()->get($event->objectId());
 *     }
 *
 * A failed delivery is retried up to three times with the same
 * `deliveryId`, so store the ones you've handled and skip repeats.
 */
final readonly class WebhookEvent
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $extra  what some events add, such as `old_item` and `update_intent` on item:updated
     */
    public function __construct(
        public string $name,
        public string $userId,
        public array $data,
        public ?Initiator $initiator,
        public ?string $triggeredAt,
        public ?string $version,
        public ?string $deliveryId,
        public array $extra = [],
    ) {}

    /**
     * The event, or null for one this SDK doesn't know yet.
     */
    public function event(): ?Event
    {
        return Event::tryFrom($this->name);
    }

    public function is(Event $event): bool
    {
        return $this->name === $event->value;
    }

    /**
     * The ID of the task, comment, project or other object that changed.
     */
    public function objectId(): ?string
    {
        $id = $this->data['id'] ?? null;

        return is_string($id) || is_int($id) ? (string) $id : null;
    }
}
