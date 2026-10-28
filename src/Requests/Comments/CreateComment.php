<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Comments;

use JustSteveKing\SdkToolbox\Bodies\Body;
use JustSteveKing\SdkToolbox\Bodies\JsonBody;
use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasBody;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Comment;
use JustSteveKing\TodoistSdk\Inputs\NewComment;

/**
 * Comments on a task or a project.
 *
 * @implements Request<Comment>
 */
final readonly class CreateComment implements HasBody, Request
{
    public function __construct(
        private NewComment $input,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return '/comments';
    }

    public function body(): Body
    {
        return new JsonBody($this->input->toArray());
    }

    public function result(Response $response): Comment
    {
        return $response->map(Comment::class);
    }
}
