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

/**
 * Rewrites a comment. Its content is the only thing that can change.
 *
 * @implements Request<Comment>
 */
final readonly class UpdateComment implements HasBody, Request
{
    public function __construct(
        private string $id,
        private string $content,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return CommentRequest::path($this->id);
    }

    public function body(): Body
    {
        return new JsonBody(['content' => $this->content]);
    }

    public function result(Response $response): Comment
    {
        return $response->map(Comment::class);
    }
}
