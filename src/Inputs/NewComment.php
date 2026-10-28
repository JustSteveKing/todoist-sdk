<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

use JustSteveKing\TodoistSdk\Data\FileAttachment;

/**
 * A comment to post on a task or a project, which Todoist needs exactly
 * one of, so there's a constructor for each.
 *
 *     NewComment::onTask('6XGgmFVcrG5RRjVr', 'Need one bottle of milk')
 *
 * `notify` takes the numeric IDs of the users to tell about it, and
 * `attachment` a file you've already uploaded or one hosted elsewhere.
 */
final readonly class NewComment
{
    /**
     * @param  list<int>|null  $notify
     */
    private function __construct(
        private string $field,
        private string $id,
        private string $content,
        private ?array $notify,
        private ?FileAttachment $attachment,
    ) {}

    /**
     * @param  list<int>|null  $notify
     */
    public static function onTask(string $taskId, string $content, ?array $notify = null, ?FileAttachment $attachment = null): self
    {
        return new self('task_id', $taskId, $content, $notify, $attachment);
    }

    /**
     * @param  list<int>|null  $notify
     */
    public static function onProject(string $projectId, string $content, ?array $notify = null, ?FileAttachment $attachment = null): self
    {
        return new self('project_id', $projectId, $content, $notify, $attachment);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'content' => $this->content,
            $this->field => $this->id,
            'uids_to_notify' => $this->notify,
            'attachment' => $this->attachment?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
