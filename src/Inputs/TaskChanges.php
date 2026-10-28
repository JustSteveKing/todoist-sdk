<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

/**
 * Changes to a task. Only what you set is sent.
 *
 * Built with methods rather than named arguments because Todoist reads
 * `null` as "clear this": a builder can tell "leave the deadline alone"
 * from "remove the deadline", and named arguments can't.
 *
 *     TaskChanges::new()->content('Buy oat milk')->dueString('friday')->withoutDeadline()
 */
final readonly class TaskChanges
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

    public function content(string $content): self
    {
        return $this->with('content', $content);
    }

    public function description(string $description): self
    {
        return $this->with('description', $description);
    }

    /**
     * @param  list<string>  $labels
     */
    public function labels(array $labels): self
    {
        return $this->with('labels', $labels);
    }

    public function priority(int $priority): self
    {
        return $this->with('priority', $priority);
    }

    public function dueString(string $dueString, ?string $lang = null): self
    {
        $changes = $this->with('due_string', $dueString);

        return $lang === null ? $changes : $changes->with('due_lang', $lang);
    }

    public function dueDate(string $date): self
    {
        return $this->with('due_date', $date);
    }

    public function dueDatetime(string $datetime): self
    {
        return $this->with('due_datetime', $datetime);
    }

    public function assignTo(int $userId): self
    {
        return $this->with('assignee_id', $userId);
    }

    public function unassign(): self
    {
        return $this->with('assignee_id', null);
    }

    /**
     * @param  'minute'|'day'  $unit
     */
    public function duration(int $amount, string $unit): self
    {
        return $this->with('duration', $amount)->with('duration_unit', $unit);
    }

    public function withoutDuration(): self
    {
        return $this->with('duration', null)->with('duration_unit', null);
    }

    public function deadline(string $date): self
    {
        return $this->with('deadline_date', $date);
    }

    public function withoutDeadline(): self
    {
        return $this->with('deadline_date', null);
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
