<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Labels;

use JustSteveKing\SdkToolbox\Errors\InvalidResponseException;
use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasQuery;
use JustSteveKing\SdkToolbox\Requests\Request;

/**
 * The names of labels on tasks shared with you, one page at a time.
 * Shared labels are only names: they have no ID, colour or order.
 *
 * @implements Request<list<string>>
 */
final readonly class ListSharedLabels implements HasQuery, Request
{
    public function __construct(
        private bool $omitPersonal = false,
        private ?int $limit = null,
    ) {}

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return '/labels/shared';
    }

    public function query(): array
    {
        return [
            'omit_personal' => $this->omitPersonal ? 'true' : null,
            'limit' => $this->limit,
        ];
    }

    public function result(Response $response): array
    {
        $names = $response->get('results');

        if (! is_array($names) || ! array_is_list($names) || array_filter($names, is_string(...)) !== $names) {
            throw new InvalidResponseException("GET /labels/shared returned {$response->status()} without a list of label names at \"results\".");
        }

        /** @var list<string> $names */
        return $names;
    }
}
