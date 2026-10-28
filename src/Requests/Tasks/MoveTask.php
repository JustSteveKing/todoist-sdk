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

/**
 * Moves a task to a project, a section, or under another task: exactly
 * one of the three.
 *
 * @implements Request<Task>
 */
final readonly class MoveTask implements HasBody, Request
{
    private function __construct(
        private string $id,
        private string $field,
        private string $target,
    ) {}

    public static function toProject(string $id, string $projectId): self
    {
        return new self($id, 'project_id', $projectId);
    }

    public static function toSection(string $id, string $sectionId): self
    {
        return new self($id, 'section_id', $sectionId);
    }

    public static function under(string $id, string $parentId): self
    {
        return new self($id, 'parent_id', $parentId);
    }

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return TaskRequest::path($this->id, '/move');
    }

    public function body(): Body
    {
        return new JsonBody([$this->field => $this->target]);
    }

    public function result(Response $response): Task
    {
        return $response->map(Task::class);
    }
}
