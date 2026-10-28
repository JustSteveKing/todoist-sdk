<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * A personal label. Tasks carry labels by name, not by ID.
 */
final readonly class Label
{
    public function __construct(
        public string $id,
        public string $name,
        public string $color,
        public ?int $order,
        public bool $isFavorite,
    ) {}
}
