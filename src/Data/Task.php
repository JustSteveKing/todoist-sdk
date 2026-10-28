<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * A task, as Todoist's API v1 returns it (the `ItemSyncView` schema).
 *
 * Timestamps are kept as the ISO 8601 strings Todoist sends. Priority runs
 * from 1 (normal) to 4 (urgent), the reverse of the "p1" to "p4" in the app.
 */
final readonly class Task
{
    /**
     * @param  list<string>  $labels
     */
    public function __construct(
        public string $id,
        public string $userId,
        public string $projectId,
        public ?string $sectionId,
        public ?string $parentId,
        public string $content,
        public string $description,
        public array $labels,
        public int $priority,
        public ?Due $due,
        public ?Duration $duration,
        public ?Deadline $deadline,
        public bool $checked,
        public bool $isDeleted,
        public bool $isCollapsed,
        public int $childOrder,
        public int $dayOrder,
        public ?string $orderKey,
        public int $noteCount,
        public int $completedCount,
        public int $postponedCount,
        public ?string $addedByUid,
        public ?string $assignedByUid,
        public ?string $responsibleUid,
        public ?string $completedByUid,
        public ?string $addedAt,
        public ?string $updatedAt,
        public ?string $completedAt,
    ) {}
}
