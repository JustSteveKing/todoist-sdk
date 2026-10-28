<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Comments;

/**
 * @internal
 */
final class CommentRequest
{
    public static function path(string $id, string $suffix = ''): string
    {
        return '/comments/'.rawurlencode($id).$suffix;
    }
}
