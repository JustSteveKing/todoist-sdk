<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Sections;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Section;

/**
 * One section.
 *
 * @implements Request<Section>
 */
final readonly class GetSection implements Request
{
    public function __construct(
        private string $id,
    ) {}

    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return SectionRequest::path($this->id);
    }

    public function result(Response $response): Section
    {
        return $response->map(Section::class);
    }
}
