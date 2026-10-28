<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Comments;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\Comment;

/**
 * One comment.
 *
 * @implements Request<Comment>
 */
final readonly class GetComment implements Request
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
        return CommentRequest::path($this->id);
    }

    public function result(Response $response): Comment
    {
        return $response->map(Comment::class);
    }
}
