<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

/**
 * Changes to a section. Only what you set is sent, and
 * `withoutDescription()` clears the description.
 */
final readonly class SectionChanges
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

    public function description(string $description): self
    {
        return $this->with('description', $description);
    }

    public function withoutDescription(): self
    {
        return $this->with('description', null);
    }

    public function order(int $order): self
    {
        return $this->with('section_order', $order);
    }

    public function collapsed(bool $collapsed = true): self
    {
        return $this->with('is_collapsed', $collapsed);
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
