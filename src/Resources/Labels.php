<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Resources;

use JustSteveKing\SdkToolbox\Pagination\CursorPagination;
use JustSteveKing\SdkToolbox\Pagination\Paginator;
use JustSteveKing\TodoistSdk\Data\Label;
use JustSteveKing\TodoistSdk\Inputs\LabelChanges;
use JustSteveKing\TodoistSdk\Inputs\NewLabel;
use JustSteveKing\TodoistSdk\Requests\Labels\CreateLabel;
use JustSteveKing\TodoistSdk\Requests\Labels\DeleteLabel;
use JustSteveKing\TodoistSdk\Requests\Labels\GetLabel;
use JustSteveKing\TodoistSdk\Requests\Labels\ListLabels;
use JustSteveKing\TodoistSdk\Requests\Labels\ListSharedLabels;
use JustSteveKing\TodoistSdk\Requests\Labels\RemoveSharedLabel;
use JustSteveKing\TodoistSdk\Requests\Labels\RenameSharedLabel;
use JustSteveKing\TodoistSdk\Requests\Labels\SearchLabels;
use JustSteveKing\TodoistSdk\Requests\Labels\UpdateLabel;
use JustSteveKing\TodoistSdk\Todoist;

/**
 * Personal labels, which have IDs, and shared labels, which are only
 * names on tasks shared with you.
 */
final readonly class Labels
{
    public function __construct(
        private Todoist $todoist,
    ) {}

    /**
     * @return Paginator<Label>
     */
    public function all(): Paginator
    {
        return $this->todoist->paginate(new ListLabels, self::cursor());
    }

    /**
     * @return Paginator<Label>
     */
    public function search(string $query): Paginator
    {
        return $this->todoist->paginate(new SearchLabels($query), self::cursor());
    }

    public function get(string $id): Label
    {
        return $this->todoist->send(new GetLabel($id));
    }

    public function create(NewLabel $label): Label
    {
        return $this->todoist->send(new CreateLabel($label));
    }

    public function update(string $id, LabelChanges $changes): Label
    {
        return $this->todoist->send(new UpdateLabel($id, $changes));
    }

    public function delete(string $id): void
    {
        $this->todoist->send(new DeleteLabel($id));
    }

    /**
     * @return Paginator<string>
     */
    public function shared(bool $omitPersonal = false): Paginator
    {
        return $this->todoist->paginate(new ListSharedLabels($omitPersonal), self::cursor());
    }

    public function renameShared(string $name, string $newName): void
    {
        $this->todoist->send(new RenameSharedLabel($name, $newName));
    }

    public function removeShared(string $name): void
    {
        $this->todoist->send(new RemoveSharedLabel($name));
    }

    private static function cursor(): CursorPagination
    {
        return new CursorPagination('next_cursor', 'cursor');
    }
}
