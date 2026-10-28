<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

/**
 * A personal label to create. Anything left null isn't sent.
 */
final readonly class NewLabel
{
    public function __construct(
        public string $name,
        public ?Color $color = null,
        public ?int $order = null,
        public ?bool $isFavorite = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'color' => $this->color?->value,
            'order' => $this->order,
            'is_favorite' => $this->isFavorite,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
