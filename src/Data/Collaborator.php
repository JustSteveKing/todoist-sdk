<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * Someone a project is shared with.
 */
final readonly class Collaborator
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
    ) {}
}
