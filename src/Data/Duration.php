<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * How long a task takes: an amount of minutes or days.
 */
final readonly class Duration
{
    public function __construct(
        public int $amount,
        public string $unit,
    ) {}
}
