<?php

declare(strict_types=1);

use JustSteveKing\TodoistSdk\Data\Task;
use JustSteveKing\TodoistSdk\Inputs\NewTask;
use JustSteveKing\TodoistSdk\Inputs\TaskChanges;

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

it('maps every field of the spec\'s task, and nothing else', function (): void {
    $parameters = array_map(fn (ReflectionParameter $p): string => $p->getName(), (new ReflectionClass(Task::class))->getConstructor()?->getParameters() ?? []);

    expect($parameters)->toEqualCanonicalizing(array_map(camel(...), properties('ItemSyncView')));
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
