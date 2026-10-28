<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * A status the user has set, shown across Todoist and Comms.
 */
final readonly class UserStatus
{
    public function __construct(
        public string $message,
        public ?string $emoji = null,
        public ?string $activeUntil = null,
        public UserStatusEffects $effects = new UserStatusEffects,
    ) {}
}
