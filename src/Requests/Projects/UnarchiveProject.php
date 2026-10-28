<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Projects;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Project;

/**
 * Brings an archived project back.
 *
 * @implements Request<Project>
 */
final readonly class UnarchiveProject implements Request
{
    public function __construct(
        private string $id,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return ProjectRequest::path($this->id, '/unarchive');
    }

    public function result(Response $response): Project
    {
        return $response->map(Project::class);
    }
}
