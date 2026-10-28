<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Labels;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasQuery;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Label;

/**
 * Personal labels whose names match a query, one page at a time.
 *
 * @implements Request<list<Label>>
 */
final readonly class SearchLabels implements HasQuery, Request
{
    public function __construct(
        private string $query,
        private ?int $limit = null,
    ) {}

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return '/labels/search';
    }

    public function query(): array
    {
        return [
            'query' => $this->query,
            'limit' => $this->limit,
        ];
    }

    public function result(Response $response): array
    {
        return $response->mapList(Label::class, at: 'results');
    }
}
