<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * A public project's settings.
 */
final readonly class ProjectAccessSettings
{
    public function __construct(
        public ?bool $hideCollaboratorDetails = null,
        public ?bool $disableDuplication = null,
    ) {}
}
