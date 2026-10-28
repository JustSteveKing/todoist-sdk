<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * The user's time zone, as Todoist describes it: the zone's name, its
 * offset from GMT, and whether daylight saving applies now.
 *
 * `isDst` is a boolean in the spec's example, but Todoist's older APIs
 * sent it as 0 or 1, so both are accepted.
 */
final readonly class TimeZone
{
    public function __construct(
        public string $timezone,
        public ?string $gmtString = null,
        public bool|int|null $isDst = null,
        public ?int $hours = null,
        public ?int $minutes = null,
    ) {}
}
