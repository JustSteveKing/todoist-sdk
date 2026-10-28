<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Webhooks;

/**
 * Who triggered an event: the user it was sent for, or a collaborator on
 * a shared project.
 */
final readonly class Initiator
{
    public function __construct(
        public string $id,
        public ?string $email,
        public ?string $fullName,
        public ?string $imageId,
        public ?bool $isPremium,
    ) {}
}
