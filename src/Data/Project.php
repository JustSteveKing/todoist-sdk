<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * A project, personal or workspace.
 *
 * Todoist returns two shapes. The fields they share come first; the ones
 * only a workspace project has default to null, and `inboxProject`, which
 * only a personal project has, defaults to false. A workspace project may
 * leave out `parentId`, so it defaults to null too. `workspaceId !== null`
 * tells you which you've got.
 *
 * Timestamps are RFC 3339 strings, as Todoist sends them.
 */
final readonly class Project
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $color,
        public ?string $icon,
        public string $viewStyle,
        public bool $isFavorite,
        public bool $isShared,
        public bool $isArchived,
        public bool $isDeleted,
        public bool $isCollapsed,
        public bool $isFrozen,
        public bool $canAssignTasks,
        public bool $canComment,
        public int $childOrder,
        public ?string $orderKey,
        public int $defaultOrder,
        public ?string $defaultOrderKey,
        public ?string $creatorUid,
        public ?string $createdAt,
        public ?string $updatedAt,
        public string $publicKey,
        public ?ProjectAccess $access,
        public ?string $role,
        public ?string $parentId = null,
        public bool $inboxProject = false,
        public ?string $workspaceId = null,
        public ?string $folderId = null,
        public ?string $status = null,
        public ?string $collaboratorRoleDefault = null,
        public ?bool $isInviteOnly = null,
        public ?bool $isLinkSharingEnabled = null,
        public ?bool $isPendingDefaultCollaboratorInvites = null,
        public ?bool $isProjectInsightsEnabled = null,
    ) {}
}
