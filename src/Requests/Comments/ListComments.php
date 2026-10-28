<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Comments;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasQuery;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Comment;

/**
 * The comments on one task or one project, one page at a time. Todoist
 * wants exactly one of the two, so there's a constructor for each.
 *
 * @implements Request<list<Comment>>
 */
final readonly class ListComments implements HasQuery, Request
{
    private function __construct(
        private string $field,
        private string $id,
        private ?int $limit,
    ) {}

    public static function forTask(string $taskId, ?int $limit = null): self
    {
        return new self('task_id', $taskId, $limit);
    }

    public static function forProject(string $projectId, ?int $limit = null): self
    {
        return new self('project_id', $projectId, $limit);
    }

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return '/comments';
    }

    public function query(): array
    {
        return [
            $this->field => $this->id,
            'limit' => $this->limit,
        ];
    }

    public function result(Response $response): array
    {
        return $response->mapList(Comment::class, at: 'results');
    }
}
