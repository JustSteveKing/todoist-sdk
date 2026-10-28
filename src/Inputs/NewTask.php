<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

/**
 * A task to create. Only `content` is required; anything left null isn't sent.
 *
 *     new NewTask('Buy milk', dueString: 'tomorrow at 9am', priority: 4)
 *
 * Due dates take one of `dueString` ("next monday", in `dueLang`),
 * `dueDate` (2026-11-16) or `dueDatetime` (an RFC 3339 time).
 */
final readonly class NewTask
{
    /**
     * @param  list<string>|null  $labels
     * @param  'minute'|'day'|null  $durationUnit
     */
    public function __construct(
        public string $content,
        public ?string $description = null,
        public ?string $projectId = null,
        public ?string $sectionId = null,
        public ?string $parentId = null,
        public ?int $order = null,
        public ?array $labels = null,
        public ?int $priority = null,
        public ?int $assigneeId = null,
        public ?string $dueString = null,
        public ?string $dueDate = null,
        public ?string $dueDatetime = null,
        public ?string $dueLang = null,
        public ?int $duration = null,
        public ?string $durationUnit = null,
        public ?string $deadlineDate = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'content' => $this->content,
            'description' => $this->description,
            'project_id' => $this->projectId,
            'section_id' => $this->sectionId,
            'parent_id' => $this->parentId,
            'order' => $this->order,
            'labels' => $this->labels,
            'priority' => $this->priority,
            'assignee_id' => $this->assigneeId,
            'due_string' => $this->dueString,
            'due_date' => $this->dueDate,
            'due_datetime' => $this->dueDatetime,
            'due_lang' => $this->dueLang,
            'duration' => $this->duration,
            'duration_unit' => $this->durationUnit,
            'deadline_date' => $this->deadlineDate,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
