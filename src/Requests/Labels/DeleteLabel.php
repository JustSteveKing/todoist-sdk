<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Labels;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;

/**
 * Deletes a personal label, removing it from every task that has it.
 *
 * @implements Request<null>
 */
final readonly class DeleteLabel implements Request
{
    public function __construct(
        private string $id,
    ) {}

    public function method(): Method
    {
        return Method::Delete;
    }

    public function path(): string
    {
        return LabelRequest::path($this->id);
    }

    public function result(Response $response): null
    {
        return null;
    }
}
