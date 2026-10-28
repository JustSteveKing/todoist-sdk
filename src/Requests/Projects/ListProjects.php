<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Projects;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasQuery;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Project;

/**
 * Active projects, one page at a time. Personal and workspace projects both come back.
 *
 * @implements Request<list<Project>>
 */
final readonly class ListProjects implements HasQuery, Request
{
    public function __construct(
        private ?int $folderId = null,
        private ?int $workspaceId = null,
        private ?int $limit = null,
    ) {}

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return '/projects';
    }

    public function query(): array
    {
        return [
            'folder_id' => $this->folderId,
            'workspace_id' => $this->workspaceId,
            'limit' => $this->limit,
        ];
    }

    public function result(Response $response): array
    {
        return $response->mapList(Project::class, at: 'results');
    }
}
