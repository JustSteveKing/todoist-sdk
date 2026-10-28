<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Sections;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;

/**
 * Deletes a section, and its tasks with it.
 *
 * @implements Request<null>
 */
final readonly class DeleteSection implements Request
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
        return SectionRequest::path($this->id);
    }

    public function result(Response $response): null
    {
        return null;
    }
}
