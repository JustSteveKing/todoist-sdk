<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * When a task is due. `string` is how the user wrote it ("every monday"),
 * and `datetime` and `timezone` are only there for a due time.
 */
final readonly class Due
{
    public function __construct(
        public string $date,
        public string $string,
        public string $lang,
        public bool $isRecurring,
        public ?string $datetime = null,
        public ?string $timezone = null,
    ) {}
}
