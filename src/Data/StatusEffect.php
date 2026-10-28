<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * One effect of a status, and where it applies. Todoist leaves the scope
 * out to mean `all`.
 */
final readonly class StatusEffect
{
    public function __construct(
        public string $scope = 'all',
    ) {}
}
