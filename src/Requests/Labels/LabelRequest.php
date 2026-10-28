<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Labels;

/**
 * @internal
 */
final class LabelRequest
{
    public static function path(string $id, string $suffix = ''): string
    {
        return '/labels/'.rawurlencode($id).$suffix;
    }
}
