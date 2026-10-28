<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

/**
 * Changes to a project. Only what you set is sent.
 *
 * A builder, like TaskChanges, because two fields read `null` as
 * something: the icon goes back to the default, and a workspace project
 * leaves its folder.
 *
 *     ProjectChanges::new()->name('Kitchen refit')->viewStyle(ViewStyle::Board)
 */
final readonly class ProjectChanges
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

    public function color(Color $color): self
    {
        return $this->with('color', $color->value);
    }

    public function icon(string $icon): self
    {
        return $this->with('icon', $icon);
    }

    public function defaultIcon(): self
    {
        return $this->with('icon', null);
    }

    public function favorite(bool $favorite = true): self
    {
        return $this->with('is_favorite', $favorite);
    }

    public function viewStyle(ViewStyle $viewStyle): self
    {
        return $this->with('view_style', $viewStyle->value);
    }

    /**
     * Personal projects only.
     */
    public function order(int $order): self
    {
        return $this->with('child_order', $order);
    }

    public function collapsed(bool $collapsed = true): self
    {
        return $this->with('is_collapsed', $collapsed);
    }

    /**
     * Workspace projects only.
     */
    public function folder(int $folderId): self
    {
        return $this->with('folder_id', $folderId);
    }

    /**
     * Workspace projects only.
     */
    public function withoutFolder(): self
    {
        return $this->with('folder_id', null);
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
