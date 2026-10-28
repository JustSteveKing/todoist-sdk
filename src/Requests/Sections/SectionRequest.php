<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Sections;

/**
 * @internal
 */
final class SectionRequest
{
    public static function path(string $id, string $suffix = ''): string
    {
        return '/sections/'.rawurlencode($id).$suffix;
    }
}
