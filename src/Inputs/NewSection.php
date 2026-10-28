<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

/**
 * A section to create in a project. Anything left null isn't sent.
 */
final readonly class NewSection
{
    public function __construct(
        public string $name,
        public string $projectId,
        public ?string $description = null,
        public ?int $order = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'project_id' => $this->projectId,
            'description' => $this->description,
            'order' => $this->order,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
