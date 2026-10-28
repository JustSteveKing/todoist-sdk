<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\OAuth;

/**
 * The permissions an app can ask a Todoist user for.
 */
enum Scope: string
{
    /** Add tasks, without reading or changing anything that exists. */
    case TaskAdd = 'task:add';

    /** Read tasks, projects, labels and filters. */
    case DataRead = 'data:read';

    /** Read and write them; includes TaskAdd and DataRead. */
    case DataReadWrite = 'data:read_write';

    /** Delete tasks, labels and filters. */
    case DataDelete = 'data:delete';

    /** Delete projects. */
    case ProjectDelete = 'project:delete';

    /** List backups without MFA. */
    case BackupsRead = 'backups:read';
}
