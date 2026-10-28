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
use JustSteveKing\TodoistSdk\Inputs\NewTask;

/**
 * Creates a task.
 *
 * @implements Request<Task>
 */
final readonly class CreateTask implements HasBody, Request
{
    public function __construct(
        private NewTask $task,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return '/tasks';
    }

    public function body(): Body
    {
        return new JsonBody($this->task->toArray());
    }

    public function result(Response $response): Task
    {
        return $response->map(Task::class);
    }
}
