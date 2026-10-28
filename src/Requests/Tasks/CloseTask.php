<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Tasks;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;

/**
 * Completes a task. A recurring task moves to its next due date instead.
 *
 * @implements Request<null>
 */
final readonly class CloseTask implements Request
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
        return TaskRequest::path($this->id, '/close');
    }

    public function result(Response $response): null
    {
        return null;
    }
}
