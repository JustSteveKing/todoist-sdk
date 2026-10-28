<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * A section of a project. Timestamps are RFC 3339 strings.
 */
final readonly class Section
{
    public function __construct(
        public string $id,
        public string $projectId,
        public string $userId,
        public string $name,
        public ?string $description,
        public int $sectionOrder,
        public ?string $orderKey,
        public bool $isCollapsed,
        public bool $isArchived,
        public bool $isDeleted,
        public string $addedAt,
        public ?string $updatedAt,
        public ?string $archivedAt,
    ) {}
}
