<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Projects;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;

/**
 * Deletes a project, and everything in it.
 *
 * @implements Request<null>
 */
final readonly class DeleteProject implements Request
{
    public function __construct(
        private string $id,
    ) {}

    public function method(): Method
    {
        return Method::Delete;
    }

    public function path(): string
    {
        return ProjectRequest::path($this->id);
    }

    public function result(Response $response): null
    {
        return null;
    }
}
