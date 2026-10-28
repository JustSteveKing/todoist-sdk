<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeResponse;
use JustSteveKing\TodoistSdk\Data\Project;
use JustSteveKing\TodoistSdk\Inputs\Color;
use JustSteveKing\TodoistSdk\Inputs\NewProject;
use JustSteveKing\TodoistSdk\Inputs\ProjectChanges;
use JustSteveKing\TodoistSdk\Inputs\ViewStyle;
use JustSteveKing\TodoistSdk\Requests\Projects\ListProjects;
use Psr\Http\Message\RequestInterface;

it('maps a personal project', function (): void {
    $project = todoist(new FakeClient(FakeResponse::json(example('project-personal'))))->projects()->get('p1');

    expect($project)->toBeInstanceOf(Project::class)
        ->and($project->name)->toBe('Inbox')
        ->and($project->workspaceId)->toBeNull()
        ->and($project->folderId)->toBeNull();
});

it('maps a workspace project, which may leave out parent_id', function (): void {
    $project = todoist(new FakeClient(FakeResponse::json(example('project-workspace'))))->projects()->get('p1');

    expect($project->workspaceId)->toBe('123456')
        ->and($project->folderId)->toBe('1234')
        ->and($project->parentId)->toBeNull()
        ->and($project->inboxProject)->toBeFalse();
});

it('maps who can see a public project', function (): void {
    $http = new FakeClient(FakeResponse::json(example('project-personal', [
        'access' => ['visibility' => 'public', 'configuration' => ['hide_collaborator_details' => true, 'disable_duplication' => false]],
    ])));

    $access = todoist($http)->projects()->get('p1')->access;

    expect($access?->visibility)->toBe('public')
        ->and($access?->configuration->hideCollaboratorDetails)->toBeTrue()
        ->and($access?->configuration->disableDuplication)->toBeFalse();
});

it('maps a restricted project, which has no settings', function (): void {
    $http = new FakeClient(FakeResponse::json(example('project-personal', ['access' => ['visibility' => 'restricted', 'configuration' => []]])));

    expect(todoist($http)->projects()->get('p1')->access?->configuration->hideCollaboratorDetails)->toBeNull();
});

it('pages through projects, filtered to a workspace', function (): void {
    $http = new FakeClient(
        page([example('project-workspace', ['id' => 'a'])], 'c2'),
        page([example('project-personal', ['id' => 'b'])]),
    );

    $ids = array_map(fn (Project $p): string => $p->id, todoist($http)->projects()->all(new ListProjects(workspaceId: 123456))->all());

    expect($ids)->toBe(['a', 'b'])
        ->and(array_map(fn (RequestInterface $r): string => $r->getUri()->getQuery(), $http->sent()))
        ->toBe(['workspace_id=123456', 'workspace_id=123456&cursor=c2']);
});

it('lists archived projects, searches, and lists collaborators', function (): void {
    $http = new FakeClient(
        page([example('project-personal')]),
        page([example('project-personal')]),
        page([example('collaborator')]),
    );
    $projects = todoist($http)->projects();

    $projects->archived()->all();
    $projects->search('launch plan')->all();
    $people = $projects->collaborators('p1')->all();

    expect($people[0]->email)->toBe('email@example.org')
        ->and(array_map(fn (RequestInterface $r): string => (string) $r->getUri(), $http->sent()))->toBe([
            'https://api.todoist.com/api/v1/projects/archived',
            'https://api.todoist.com/api/v1/projects/search?query=launch%20plan',
            'https://api.todoist.com/api/v1/projects/p1/collaborators',
        ]);
});

it('creates a project with typed colour and view style', function (): void {
    $http = new FakeClient(FakeResponse::json(example('project-personal')));

    todoist($http)->projects()->create(new NewProject('Kitchen refit', color: Color::Teal, viewStyle: ViewStyle::Board));

    $http->assertSentTo('POST', '/api/v1/projects');
    expect(json_decode((string) $http->last()?->getBody(), true))
        ->toBe(['name' => 'Kitchen refit', 'color' => 'teal', 'view_style' => 'board']);
});

it('updates a project, sending null only where Todoist reads it as a reset', function (): void {
    $http = new FakeClient(FakeResponse::json(example('project-workspace')));

    todoist($http)->projects()->update('p1', ProjectChanges::new()->name('Renamed')->defaultIcon()->withoutFolder()->favorite());

    $http->assertSentTo('POST', '/api/v1/projects/p1');
    expect(json_decode((string) $http->last()?->getBody(), true))
        ->toBe(['name' => 'Renamed', 'icon' => null, 'folder_id' => null, 'is_favorite' => true]);
});

it('archives, unarchives and deletes', function (): void {
    $http = new FakeClient(FakeResponse::json(example('project-personal', ['is_archived' => true])), FakeResponse::json(example('project-personal')), FakeResponse::json([]));
    $projects = todoist($http)->projects();

    expect($projects->archive('p1')->isArchived)->toBeTrue()
        ->and($projects->unarchive('p1')->isArchived)->toBeFalse();
    $projects->delete('p1');

    $http->assertSentInOrder(['POST /api/v1/projects/p1/archive', 'POST /api/v1/projects/p1/unarchive', 'DELETE /api/v1/projects/p1']);
});
