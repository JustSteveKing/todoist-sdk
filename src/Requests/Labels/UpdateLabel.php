<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Labels;

use JustSteveKing\SdkToolbox\Bodies\Body;
use JustSteveKing\SdkToolbox\Bodies\JsonBody;
use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasBody;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Label;
use JustSteveKing\TodoistSdk\Inputs\LabelChanges;

/**
 * Changes a personal label. Only what the changes set is sent.
 *
 * @implements Request<Label>
 */
final readonly class UpdateLabel implements HasBody, Request
{
    public function __construct(
        private string $id,
        private LabelChanges $input,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return LabelRequest::path($this->id);
    }

    public function body(): Body
    {
        return new JsonBody($this->input->toArray());
    }

    public function result(Response $response): Label
    {
        return $response->map(Label::class);
    }
}
