<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Tasks;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasQuery;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Task;

/**
 * Active tasks, one page at a time; filters narrow them.
 *
 * @implements Request<list<Task>>
 */
final readonly class ListTasks implements HasQuery, Request
{
    /**
     * @param  list<string>|null  $ids
     */
    public function __construct(
        private ?string $projectId = null,
        private ?string $sectionId = null,
        private ?string $parentId = null,
        private ?string $label = null,
        private ?array $ids = null,
        private ?int $limit = null,
    ) {}

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return '/tasks';
    }

    public function query(): array
    {
        return [
            'project_id' => $this->projectId,
            'section_id' => $this->sectionId,
            'parent_id' => $this->parentId,
            'label' => $this->label,
            'ids' => $this->ids === null ? null : implode(',', $this->ids),
            'limit' => $this->limit,
        ];
    }

    public function result(Response $response): array
    {
        return $response->mapList(Task::class, at: 'results');
    }
}
