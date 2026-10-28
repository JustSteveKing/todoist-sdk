<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Sections;

use JustSteveKing\SdkToolbox\Bodies\Body;
use JustSteveKing\SdkToolbox\Bodies\JsonBody;
use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasBody;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Section;
use JustSteveKing\TodoistSdk\Inputs\SectionChanges;

/**
 * Changes a section. Only what the changes set is sent.
 *
 * @implements Request<Section>
 */
final readonly class UpdateSection implements HasBody, Request
{
    public function __construct(
        private string $id,
        private SectionChanges $input,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return SectionRequest::path($this->id);
    }

    public function body(): Body
    {
        return new JsonBody($this->input->toArray());
    }

    public function result(Response $response): Section
    {
        return $response->map(Section::class);
    }
}
