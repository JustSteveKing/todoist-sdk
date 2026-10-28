<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * Who can see a project: `restricted`, `team` or `public`. Only a public
 * project has settings, so for the others both of these stay null.
 */
final readonly class ProjectAccess
{
    public function __construct(
        public string $visibility,
        public ProjectAccessSettings $configuration = new ProjectAccessSettings,
    ) {}
}
