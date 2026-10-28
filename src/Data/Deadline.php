<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

final readonly class Deadline
{
    public function __construct(
        public string $date,
        public ?string $lang = null,
    ) {}
}
