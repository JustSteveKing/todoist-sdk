<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Tasks;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Task;

/**
 * One task by its ID.
 *
 * @implements Request<Task>
 */
final readonly class GetTask implements Request
{
    public function __construct(
        private string $id,
    ) {}

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return TaskRequest::path($this->id);
    }

    public function result(Response $response): Task
    {
        return $response->map(Task::class);
    }
}
