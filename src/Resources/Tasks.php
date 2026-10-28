<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Resources;

use JustSteveKing\SdkToolbox\Pagination\CursorPagination;
use JustSteveKing\SdkToolbox\Pagination\Paginator;
use JustSteveKing\TodoistSdk\Data\Task;
use JustSteveKing\TodoistSdk\Inputs\NewTask;
use JustSteveKing\TodoistSdk\Inputs\TaskChanges;
use JustSteveKing\TodoistSdk\Requests\Tasks\CloseTask;
use JustSteveKing\TodoistSdk\Requests\Tasks\CreateTask;
use JustSteveKing\TodoistSdk\Requests\Tasks\DeleteTask;
use JustSteveKing\TodoistSdk\Requests\Tasks\GetTask;
use JustSteveKing\TodoistSdk\Requests\Tasks\ListTasks;
use JustSteveKing\TodoistSdk\Requests\Tasks\MoveTask;
use JustSteveKing\TodoistSdk\Requests\Tasks\ReopenTask;
use JustSteveKing\TodoistSdk\Requests\Tasks\UpdateTask;
use JustSteveKing\TodoistSdk\Todoist;

final readonly class Tasks
{
    public function __construct(
        private Todoist $todoist,
    ) {}

    /**
     * Every active task matching the filters, fetched a page at a time as
     * you iterate. Todoist pages by `next_cursor`.
     *
     * @return Paginator<Task>
     */
    public function all(?ListTasks $filters = null): Paginator
    {
        return $this->todoist->paginate($filters ?? new ListTasks, new CursorPagination('next_cursor', 'cursor'));
    }

    public function get(string $id): Task
    {
        return $this->todoist->send(new GetTask($id));
    }

    public function create(NewTask $task): Task
    {
        return $this->todoist->send(new CreateTask($task));
    }

    public function update(string $id, TaskChanges $changes): Task
    {
        return $this->todoist->send(new UpdateTask($id, $changes));
    }

    public function moveToProject(string $id, string $projectId): Task
    {
        return $this->todoist->send(MoveTask::toProject($id, $projectId));
    }

    public function moveToSection(string $id, string $sectionId): Task
    {
        return $this->todoist->send(MoveTask::toSection($id, $sectionId));
    }

    public function moveUnder(string $id, string $parentId): Task
    {
        return $this->todoist->send(MoveTask::under($id, $parentId));
    }

    public function close(string $id): void
    {
        $this->todoist->send(new CloseTask($id));
    }

    public function reopen(string $id): void
    {
        $this->todoist->send(new ReopenTask($id));
    }

    public function delete(string $id): void
    {
        $this->todoist->send(new DeleteTask($id));
    }
}
