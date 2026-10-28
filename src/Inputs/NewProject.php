<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

/**
 * A project to create. Only `name` is required; anything left null isn't sent.
 *
 *     new NewProject('Kitchen refit', color: Color::Teal, viewStyle: ViewStyle::Board)
 *
 * Give a `workspaceId` to create a workspace project instead of a personal one.
 */
final readonly class NewProject
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $parentId = null,
        public ?Color $color = null,
        public ?string $icon = null,
        public ?bool $isFavorite = null,
        public ?ViewStyle $viewStyle = null,
        public ?int $workspaceId = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'color' => $this->color?->value,
            'icon' => $this->icon,
            'is_favorite' => $this->isFavorite,
            'view_style' => $this->viewStyle?->value,
            'workspace_id' => $this->workspaceId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
