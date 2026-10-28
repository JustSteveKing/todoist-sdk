<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * What a status pauses while it's active: notifications, and Karma and
 * streaks (as Vacation Mode does). Each is null when the status doesn't
 * pause it.
 */
final readonly class UserStatusEffects
{
    public function __construct(
        public ?StatusEffect $pauseNotifications = null,
        public ?StatusEffect $pauseActivity = null,
    ) {}
}
