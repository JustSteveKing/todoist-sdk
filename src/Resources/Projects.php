<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Resources;

use JustSteveKing\SdkToolbox\Pagination\CursorPagination;
use JustSteveKing\SdkToolbox\Pagination\Paginator;
use JustSteveKing\TodoistSdk\Data\Collaborator;
use JustSteveKing\TodoistSdk\Data\Project;
use JustSteveKing\TodoistSdk\Inputs\NewProject;
use JustSteveKing\TodoistSdk\Inputs\ProjectChanges;
use JustSteveKing\TodoistSdk\Requests\Projects\ArchiveProject;
use JustSteveKing\TodoistSdk\Requests\Projects\CreateProject;
use JustSteveKing\TodoistSdk\Requests\Projects\DeleteProject;
use JustSteveKing\TodoistSdk\Requests\Projects\GetProject;
use JustSteveKing\TodoistSdk\Requests\Projects\ListArchivedProjects;
use JustSteveKing\TodoistSdk\Requests\Projects\ListCollaborators;
use JustSteveKing\TodoistSdk\Requests\Projects\ListProjects;
use JustSteveKing\TodoistSdk\Requests\Projects\SearchProjects;
use JustSteveKing\TodoistSdk\Requests\Projects\UnarchiveProject;
use JustSteveKing\TodoistSdk\Requests\Projects\UpdateProject;
use JustSteveKing\TodoistSdk\Todoist;

final readonly class Projects
{
    public function __construct(
        private Todoist $todoist,
    ) {}

    /**
     * @return Paginator<Project>
     */
    public function all(?ListProjects $filters = null): Paginator
    {
        return $this->todoist->paginate($filters ?? new ListProjects, self::cursor());
    }

    /**
     * @return Paginator<Project>
     */
    public function archived(): Paginator
    {
        return $this->todoist->paginate(new ListArchivedProjects, self::cursor());
    }

    /**
     * @return Paginator<Project>
     */
    public function search(string $query): Paginator
    {
        return $this->todoist->paginate(new SearchProjects($query), self::cursor());
    }

    public function get(string $id): Project
    {
        return $this->todoist->send(new GetProject($id));
    }

    public function create(NewProject $project): Project
    {
        return $this->todoist->send(new CreateProject($project));
    }

    public function update(string $id, ProjectChanges $changes): Project
    {
        return $this->todoist->send(new UpdateProject($id, $changes));
    }

    public function archive(string $id): Project
    {
        return $this->todoist->send(new ArchiveProject($id));
    }

    public function unarchive(string $id): Project
    {
        return $this->todoist->send(new UnarchiveProject($id));
    }

    public function delete(string $id): void
    {
        $this->todoist->send(new DeleteProject($id));
    }

    /**
     * @return Paginator<Collaborator>
     */
    public function collaborators(string $id): Paginator
    {
        return $this->todoist->paginate(new ListCollaborators($id), self::cursor());
    }

    private static function cursor(): CursorPagination
    {
        return new CursorPagination('next_cursor', 'cursor');
    }
}
