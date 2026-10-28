<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Sections;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Section;

/**
 * Brings an archived section back.
 *
 * @implements Request<Section>
 */
final readonly class UnarchiveSection implements Request
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
        return SectionRequest::path($this->id, '/unarchive');
    }

    public function result(Response $response): Section
    {
        return $response->map(Section::class);
    }
}
