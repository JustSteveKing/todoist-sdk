<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Projects;

use JustSteveKing\SdkToolbox\Bodies\Body;
use JustSteveKing\SdkToolbox\Bodies\JsonBody;
use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasBody;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Project;
use JustSteveKing\TodoistSdk\Inputs\NewProject;

/**
 * Creates a project: a personal one, or a workspace one when the input names a workspace.
 *
 * @implements Request<Project>
 */
final readonly class CreateProject implements HasBody, Request
{
    public function __construct(
        private NewProject $input,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return '/projects';
    }

    public function body(): Body
    {
        return new JsonBody($this->input->toArray());
    }

    public function result(Response $response): Project
    {
        return $response->map(Project::class);
    }
}
