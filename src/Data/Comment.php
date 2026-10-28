<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * A comment on a task or a project: one of `itemId` (Todoist's name for
 * a task's ID) and `projectId` is set.
 */
final readonly class Comment
{
    /**
     * @param  list<string>|null  $uidsToNotify
     * @param  array<string, list<string>>|null  $reactions  user IDs by emoji
     */
    public function __construct(
        public string $id,
        public ?string $postedUid,
        public ?string $postedAt,
        public bool $isDeleted,
        public ?FileAttachment $fileAttachment,
        public ?array $uidsToNotify,
        public ?array $reactions,
        public string $content = '',
        public ?string $itemId = null,
        public ?string $projectId = null,
    ) {}
}
