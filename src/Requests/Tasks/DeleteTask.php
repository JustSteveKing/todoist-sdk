<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Tasks;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;

/**
 * Deletes a task and its subtasks.
 *
 * @implements Request<null>
 */
final readonly class DeleteTask implements Request
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
        return TaskRequest::path($this->id);
    }

    public function result(Response $response): null
    {
        return null;
    }
}
