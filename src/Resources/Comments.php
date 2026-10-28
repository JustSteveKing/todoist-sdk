<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Resources;

use JustSteveKing\SdkToolbox\Pagination\CursorPagination;
use JustSteveKing\SdkToolbox\Pagination\Paginator;
use JustSteveKing\TodoistSdk\Data\Comment;
use JustSteveKing\TodoistSdk\Inputs\NewComment;
use JustSteveKing\TodoistSdk\Requests\Comments\CreateComment;
use JustSteveKing\TodoistSdk\Requests\Comments\DeleteComment;
use JustSteveKing\TodoistSdk\Requests\Comments\GetComment;
use JustSteveKing\TodoistSdk\Requests\Comments\ListComments;
use JustSteveKing\TodoistSdk\Requests\Comments\UpdateComment;
use JustSteveKing\TodoistSdk\Todoist;

final readonly class Comments
{
    public function __construct(
        private Todoist $todoist,
    ) {}

    /**
     * @return Paginator<Comment>
     */
    public function onTask(string $taskId): Paginator
    {
        return $this->todoist->paginate(ListComments::forTask($taskId), self::cursor());
    }

    /**
     * @return Paginator<Comment>
     */
    public function onProject(string $projectId): Paginator
    {
        return $this->todoist->paginate(ListComments::forProject($projectId), self::cursor());
    }

    public function get(string $id): Comment
    {
        return $this->todoist->send(new GetComment($id));
    }

    public function create(NewComment $comment): Comment
    {
        return $this->todoist->send(new CreateComment($comment));
    }

    public function update(string $id, string $content): Comment
    {
        return $this->todoist->send(new UpdateComment($id, $content));
    }

    public function delete(string $id): void
    {
        $this->todoist->send(new DeleteComment($id));
    }

    private static function cursor(): CursorPagination
    {
        return new CursorPagination('next_cursor', 'cursor');
    }
}
