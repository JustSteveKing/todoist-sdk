<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Webhooks;

/**
 * The events a Todoist app can subscribe to, configured in the App
 * Management Console. Todoist calls tasks items and comments notes.
 */
enum Event: string
{
    case TaskAdded = 'item:added';
    case TaskUpdated = 'item:updated';
    case TaskDeleted = 'item:deleted';
    case TaskCompleted = 'item:completed';
    case TaskUncompleted = 'item:uncompleted';
    case CommentAdded = 'note:added';
    case CommentUpdated = 'note:updated';
    case CommentDeleted = 'note:deleted';
    case ProjectAdded = 'project:added';
    case ProjectUpdated = 'project:updated';
    case ProjectDeleted = 'project:deleted';
    case ProjectArchived = 'project:archived';
    case ProjectUnarchived = 'project:unarchived';
    case SectionAdded = 'section:added';
    case SectionUpdated = 'section:updated';
    case SectionDeleted = 'section:deleted';
    case SectionArchived = 'section:archived';
    case SectionUnarchived = 'section:unarchived';
    case LabelAdded = 'label:added';
    case LabelDeleted = 'label:deleted';
    case LabelUpdated = 'label:updated';
    case FilterAdded = 'filter:added';
    case FilterDeleted = 'filter:deleted';
    case FilterUpdated = 'filter:updated';
    case ReminderFired = 'reminder:fired';
}
