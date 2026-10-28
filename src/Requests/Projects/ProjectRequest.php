<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Projects;

/**
 * @internal
 */
final class ProjectRequest
{
    public static function path(string $id, string $suffix = ''): string
    {
        return '/projects/'.rawurlencode($id).$suffix;
    }
}
