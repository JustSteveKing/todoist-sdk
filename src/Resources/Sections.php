<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Resources;

use JustSteveKing\SdkToolbox\Pagination\CursorPagination;
use JustSteveKing\SdkToolbox\Pagination\Paginator;
use JustSteveKing\TodoistSdk\Data\Section;
use JustSteveKing\TodoistSdk\Inputs\NewSection;
use JustSteveKing\TodoistSdk\Inputs\SectionChanges;
use JustSteveKing\TodoistSdk\Requests\Sections\ArchiveSection;
use JustSteveKing\TodoistSdk\Requests\Sections\CreateSection;
use JustSteveKing\TodoistSdk\Requests\Sections\DeleteSection;
use JustSteveKing\TodoistSdk\Requests\Sections\GetSection;
use JustSteveKing\TodoistSdk\Requests\Sections\ListSections;
use JustSteveKing\TodoistSdk\Requests\Sections\SearchSections;
use JustSteveKing\TodoistSdk\Requests\Sections\UnarchiveSection;
use JustSteveKing\TodoistSdk\Requests\Sections\UpdateSection;
use JustSteveKing\TodoistSdk\Todoist;

final readonly class Sections
{
    public function __construct(
        private Todoist $todoist,
    ) {}

    /**
     * Every active section, or a single project's.
     *
     * @return Paginator<Section>
     */
    public function all(?string $projectId = null): Paginator
    {
        return $this->todoist->paginate(new ListSections($projectId), self::cursor());
    }

    /**
     * @return Paginator<Section>
     */
    public function search(string $query, ?string $projectId = null): Paginator
    {
        return $this->todoist->paginate(new SearchSections($query, $projectId), self::cursor());
    }

    public function get(string $id): Section
    {
        return $this->todoist->send(new GetSection($id));
    }

    public function create(NewSection $section): Section
    {
        return $this->todoist->send(new CreateSection($section));
    }

    public function update(string $id, SectionChanges $changes): Section
    {
        return $this->todoist->send(new UpdateSection($id, $changes));
    }

    public function archive(string $id): Section
    {
        return $this->todoist->send(new ArchiveSection($id));
    }

    public function unarchive(string $id): Section
    {
        return $this->todoist->send(new UnarchiveSection($id));
    }

    public function delete(string $id): void
    {
        $this->todoist->send(new DeleteSection($id));
    }

    private static function cursor(): CursorPagination
    {
        return new CursorPagination('next_cursor', 'cursor');
    }
}
