<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Sections;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasQuery;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Section;

/**
 * Sections whose names match a query, one page at a time.
 *
 * @implements Request<list<Section>>
 */
final readonly class SearchSections implements HasQuery, Request
{
    public function __construct(
        private string $query,
        private ?string $projectId = null,
        private ?int $limit = null,
    ) {}

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return '/sections/search';
    }

    public function query(): array
    {
        return [
            'query' => $this->query,
            'project_id' => $this->projectId,
            'limit' => $this->limit,
        ];
    }

    public function result(Response $response): array
    {
        return $response->mapList(Section::class, at: 'results');
    }
}
