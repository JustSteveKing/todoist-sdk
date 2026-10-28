<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

/**
 * Changes to a personal label. Only what you set is sent.
 */
final readonly class LabelChanges
{
    /**
     * @param  array<string, mixed>  $changes
     */
    private function __construct(
        private array $changes = [],
    ) {}

    public static function new(): self
    {
        return new self;
    }

    public function name(string $name): self
    {
        return $this->with('name', $name);
    }

    public function color(Color $color): self
    {
        return $this->with('color', $color->value);
    }

    public function order(int $order): self
    {
        return $this->with('order', $order);
    }

    public function favorite(bool $favorite = true): self
    {
        return $this->with('is_favorite', $favorite);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->changes;
    }

    private function with(string $field, mixed $value): self
    {
        return new self([...$this->changes, $field => $value]);
    }
}
