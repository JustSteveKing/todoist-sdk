<?php

declare(strict_types=1);

use JustSteveKing\TodoistSdk\Data\Collaborator;
use JustSteveKing\TodoistSdk\Data\Comment;
use JustSteveKing\TodoistSdk\Data\FileAttachment;
use JustSteveKing\TodoistSdk\Data\Label;
use JustSteveKing\TodoistSdk\Data\Project;
use JustSteveKing\TodoistSdk\Data\Section;
use JustSteveKing\TodoistSdk\Data\Task;
use JustSteveKing\TodoistSdk\Data\User;
use JustSteveKing\TodoistSdk\Data\UserStatus;
use JustSteveKing\TodoistSdk\Inputs\Color;
use JustSteveKing\TodoistSdk\Inputs\LabelChanges;
use JustSteveKing\TodoistSdk\Inputs\NewComment;
use JustSteveKing\TodoistSdk\Inputs\NewLabel;
use JustSteveKing\TodoistSdk\Inputs\NewProject;
use JustSteveKing\TodoistSdk\Inputs\NewSection;
use JustSteveKing\TodoistSdk\Inputs\NewTask;
use JustSteveKing\TodoistSdk\Inputs\ProjectChanges;
use JustSteveKing\TodoistSdk\Inputs\SectionChanges;
use JustSteveKing\TodoistSdk\Inputs\TaskChanges;
use JustSteveKing\TodoistSdk\Inputs\ViewStyle;
use JustSteveKing\TodoistSdk\OAuth\Scope;

/**
 * The SDK against Todoist's OpenAPI spec (spec/todoist-openapi.json), so a
 * field the spec has and the models lack, or the reverse, fails a test
 * rather than surfacing in someone's application.
 */
function camel(string $snake): string
{
    return lcfirst(str_replace('_', '', ucwords($snake, '_')));
}

/**
 * @return list<string>
 */
function properties(string $schema): array
{
    $schemas = spec()['components']['schemas'] ?? [];

    return array_keys(is_array($schemas) && is_array($schemas[$schema]['properties'] ?? null) ? $schemas[$schema]['properties'] : []);
}

function requestSchema(string $path, string $method): string
{
    $ref = spec()['paths'][$path][$method]['requestBody']['content']['application/json']['schema']['$ref'] ?? '';

    return basename(is_string($ref) ? $ref : '');
}

/**
 * @param  class-string  $class
 * @return list<string>
 */
function parameters(string $class): array
{
    return array_map(fn (ReflectionParameter $p): string => $p->getName(), (new ReflectionClass($class))->getConstructor()?->getParameters() ?? []);
}

it('maps every field of the spec\'s models, and nothing else', function (string $class, array $schemas): void {
    $fields = array_values(array_unique(array_merge(...array_map(properties(...), $schemas))));

    expect(parameters($class))->toEqualCanonicalizing(array_map(camel(...), $fields));
})->with([
    'task' => [Task::class, ['ItemSyncView']],
    'project, personal or workspace' => [Project::class, ['PersonalProjectSyncView', 'WorkspaceProjectSyncView']],
    'section' => [Section::class, ['SectionSyncView']],
    'label' => [Label::class, ['LabelRestView']],
    'comment, on a task or a project' => [Comment::class, ['ItemNoteSyncView', 'ProjectNoteSyncView']],
    'collaborator' => [Collaborator::class, ['Collaborator']],
    'file attachment, as an upload returns it' => [FileAttachment::class, ['UploadResult']],
    'user status' => [UserStatus::class, ['UserStatus']],
]);

it('maps every field of the spec\'s user but the ones left out on purpose', function (): void {
    // The user's API token, twice, a device push token, and two objects
    // the spec leaves untyped. User's docblock says why.
    $omitted = ['token', 'websocket_url', 'urgent_reminder_device', 'features', 'joinable_workspace'];

    expect(array_intersect($omitted, properties('UserJSON')))->toEqualCanonicalizing($omitted)
        ->and(parameters(User::class))->toEqualCanonicalizing(array_map(camel(...), array_values(array_diff(properties('UserJSON'), $omitted))));
});

it('sends only fields the spec accepts when creating a task', function (): void {
    $everything = new NewTask('x', 'd', 'p', 's', 'pa', 1, ['l'], 4, 1, 'tomorrow', '2026-11-16', '2026-11-16T09:00:00Z', 'en', 30, 'minute', '2026-11-20');

    expect(array_diff(array_keys($everything->toArray()), properties(requestSchema('/api/v1/tasks', 'post'))))->toBe([]);
});

it('sends only fields the spec accepts when updating a task', function (): void {
    $everything = TaskChanges::new()->content('x')->description('d')->labels(['l'])->priority(4)
        ->dueString('tomorrow', 'en')->dueDate('2026-11-16')->dueDatetime('2026-11-16T09:00:00Z')
        ->assignTo(1)->duration(30, 'minute')->deadline('2026-11-20')->collapsed();

    expect(array_diff(array_keys($everything->toArray()), properties(requestSchema('/api/v1/tasks/{task_id}', 'post'))))->toBe([]);
});

it('sends only fields the spec accepts', function (array $body, string $path): void {
    expect(array_diff(array_keys($body), properties(requestSchema($path, 'post'))))->toBe([]);
})->with([
    'new project' => fn (): array => [(new NewProject('x', 'd', 'p', Color::Teal, 'lightbulb', true, ViewStyle::Board, 1))->toArray(), '/api/v1/projects'],
    'project changes' => fn (): array => [ProjectChanges::new()->name('x')->description('d')->color(Color::Red)->icon('flag')->favorite()
        ->viewStyle(ViewStyle::List)->order(1)->collapsed()->folder(1)->toArray(), '/api/v1/projects/{project_id}'],
    'new section' => fn (): array => [(new NewSection('x', 'p', 'd', 1))->toArray(), '/api/v1/sections'],
    'section changes' => fn (): array => [SectionChanges::new()->name('x')->description('d')->order(1)->collapsed()->toArray(), '/api/v1/sections/{section_id}'],
    'new label' => fn (): array => [(new NewLabel('x', Color::Grey, 1, true))->toArray(), '/api/v1/labels'],
    'label changes' => fn (): array => [LabelChanges::new()->name('x')->color(Color::Red)->order(1)->favorite()->toArray(), '/api/v1/labels/{label_id}'],
    'comment on a task' => fn (): array => [NewComment::onTask('t', 'x', [1], new FileAttachment('u'))->toArray(), '/api/v1/comments'],
    'comment on a project' => fn (): array => [NewComment::onProject('p', 'x')->toArray(), '/api/v1/comments'],
]);

it('knows every colour Todoist accepts', function (): void {
    $colours = spec()['components']['schemas']['APIColorID']['anyOf'][0]['enum'] ?? [];

    expect(array_map(fn (Color $c): string => $c->value, Color::cases()))->toEqualCanonicalizing($colours);
});

it('knows every OAuth scope Todoist documents', function (): void {
    $guide = '';

    foreach (spec()['tags'] ?? [] as $tag) {
        if (($tag['name'] ?? null) === 'Authorization') {
            $guide = (string) $tag['description'];
        }
    }

    $section = (string) strstr(strstr($guide, '#### Permission scopes') ?: '', '#### Potential errors', true);
    preg_match_all('/^\| ([a-z_]+:[a-z_]+)\s+\|/m', $section, $matches);

    expect(array_map(fn (Scope $s): string => $s->value, Scope::cases()))->toEqualCanonicalizing($matches[1]);
});

it('knows every view style Todoist accepts', function (): void {
    expect(array_map(fn (ViewStyle $v): string => $v->value, ViewStyle::cases()))
        ->toEqualCanonicalizing(spec()['components']['schemas']['ProjectViewStyleV1']['enum'] ?? []);
});
