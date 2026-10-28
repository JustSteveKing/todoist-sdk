<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Projects;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasQuery;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Collaborator;

/**
 * The people a project is shared with, one page at a time.
 *
 * @implements Request<list<Collaborator>>
 */
final readonly class ListCollaborators implements HasQuery, Request
{
    public function __construct(
        private string $projectId,
        private ?int $limit = null,
    ) {}

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return ProjectRequest::path($this->projectId, '/collaborators');
    }

    public function query(): array
    {
        return [
            'limit' => $this->limit,
        ];
    }

    public function result(Response $response): array
    {
        return $response->mapList(Collaborator::class, at: 'results');
    }
}
