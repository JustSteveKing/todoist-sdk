<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Sections;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Section;

/**
 * Archives a section, and its tasks with it.
 *
 * @implements Request<Section>
 */
final readonly class ArchiveSection implements Request
{
    public function __construct(
        private string $id,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return SectionRequest::path($this->id, '/archive');
    }

    public function result(Response $response): Section
    {
        return $response->map(Section::class);
    }
}
