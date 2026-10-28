<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Tasks;

/**
 * @internal
 */
final class TaskRequest
{
    public static function path(string $id, string $suffix = ''): string
    {
        return '/tasks/'.rawurlencode($id).$suffix;
    }
}
