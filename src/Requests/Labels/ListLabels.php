<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Labels;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasQuery;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Label;

/**
 * Personal labels, one page at a time.
 *
 * @implements Request<list<Label>>
 */
final readonly class ListLabels implements HasQuery, Request
{
    public function __construct(
        private ?int $limit = null,
    ) {}

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return '/labels';
    }

    public function query(): array
    {
        return [
            'limit' => $this->limit,
        ];
    }

    public function result(Response $response): array
    {
        return $response->mapList(Label::class, at: 'results');
    }
}
