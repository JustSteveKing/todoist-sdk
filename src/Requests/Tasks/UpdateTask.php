<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Tasks;

use JustSteveKing\SdkToolbox\Bodies\Body;
use JustSteveKing\SdkToolbox\Bodies\JsonBody;
use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasBody;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Task;
use JustSteveKing\TodoistSdk\Inputs\TaskChanges;

/**
 * Changes a task. Only what the changes set is sent.
 *
 * @implements Request<Task>
 */
final readonly class UpdateTask implements HasBody, Request
{
    public function __construct(
        private string $id,
        private TaskChanges $changes,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return TaskRequest::path($this->id);
    }

    public function body(): Body
    {
        return new JsonBody($this->changes->toArray());
    }

    public function result(Response $response): Task
    {
        return $response->map(Task::class);
    }
}
