<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeResponse;
use JustSteveKing\TodoistSdk\Data\Deadline;
use JustSteveKing\TodoistSdk\Data\Duration;
use JustSteveKing\TodoistSdk\Data\Task;
use JustSteveKing\TodoistSdk\Inputs\NewTask;
use JustSteveKing\TodoistSdk\Inputs\TaskChanges;
use JustSteveKing\TodoistSdk\Requests\Tasks\ListTasks;
use JustSteveKing\TodoistSdk\Todoist;
use Psr\Http\Message\RequestInterface;

it('gets a task, mapping every field from the spec example', function (): void {
    $http = new FakeClient(FakeResponse::json(taskJson()));

    $task = todoist($http)->tasks()->get('6XGgmFVcrG5RRjVr');

    expect($task)->toBeInstanceOf(Task::class)
        ->and($task->id)->toBe('6XGgmFVcrG5RRjVr')
        ->and($task->projectId)->toBe('6XGgm6PHrGgMpCFX')
        ->and($task->labels)->toBe(['priority'])
        ->and($task->duration)->toEqual(new Duration(30, 'minute'))
        ->and($task->deadline)->toEqual(new Deadline('2025-02-12', 'en'))
        ->and($task->due?->string)->toBe('tomorrow')
        ->and($task->due?->isRecurring)->toBeFalse();

    $request = $http->last();
    expect((string) $request?->getUri())->toBe('https://api.todoist.com/api/v1/tasks/6XGgmFVcrG5RRjVr')
        ->and($request?->getHeaderLine('Authorization'))->toBe('Bearer test-token')
        ->and($request?->getHeaderLine('User-Agent'))->toStartWith('todoist-sdk-php/'.Todoist::VERSION.' sdk-toolbox/');
});

it('maps tasks with no due date, duration or deadline', function (): void {
    $http = new FakeClient(FakeResponse::json(taskJson(['due' => null, 'duration' => null, 'deadline' => null, 'section_id' => null])));

    $task = todoist($http)->tasks()->get('x');

    expect($task->due)->toBeNull()->and($task->duration)->toBeNull()->and($task->deadline)->toBeNull();
});

it('pages through every task by next_cursor', function (): void {
    $http = new FakeClient(
        FakeResponse::json(['results' => [taskJson(['id' => 'a']), taskJson(['id' => 'b'])], 'next_cursor' => 'c2']),
        FakeResponse::json(['results' => [taskJson(['id' => 'c'])], 'next_cursor' => null]),
    );

    $ids = array_map(fn (Task $t): string => $t->id, todoist($http)->tasks()->all(new ListTasks(projectId: 'p1', label: 'work'))->all());

    expect($ids)->toBe(['a', 'b', 'c'])
        ->and(array_map(fn (RequestInterface $r): string => $r->getUri()->getQuery(), $http->sent()))
        ->toBe(['project_id=p1&label=work', 'project_id=p1&label=work&cursor=c2']);
});

it('sends ids as a comma-separated list', function (): void {
    $http = new FakeClient(FakeResponse::json(['results' => [], 'next_cursor' => null]));

    todoist($http)->send(new ListTasks(ids: ['a', 'b']));

    expect(urldecode($http->last()?->getUri()->getQuery() ?? ''))->toBe('ids=a,b');
});

it('creates a task, sending only what was set', function (): void {
    $http = new FakeClient(FakeResponse::json(taskJson(['content' => 'Buy milk'])));

    $task = todoist($http)->tasks()->create(new NewTask('Buy milk', dueString: 'tomorrow at 9am', priority: 4, labels: ['shopping']));

    expect($task->content)->toBe('Buy milk');
    $http->assertSentTo('POST', '/api/v1/tasks');
    expect(json_decode((string) $http->last()?->getBody(), true))->toBe([
        'content' => 'Buy milk',
        'labels' => ['shopping'],
        'priority' => 4,
        'due_string' => 'tomorrow at 9am',
    ]);
});

it('updates a task, telling "leave it" from "clear it"', function (): void {
    $http = new FakeClient(FakeResponse::json(taskJson()));

    todoist($http)->tasks()->update('t1', TaskChanges::new()->content('Buy oat milk')->withoutDeadline()->unassign());

    $http->assertSentTo('POST', '/api/v1/tasks/t1');
    expect(json_decode((string) $http->last()?->getBody(), true))->toBe([
        'content' => 'Buy oat milk',
        'deadline_date' => null,
        'assignee_id' => null,
    ]);
});

it('moves a task to exactly one place', function (): void {
    $http = new FakeClient(FakeResponse::json(taskJson()), FakeResponse::json(taskJson()), FakeResponse::json(taskJson()));
    $tasks = todoist($http)->tasks();

    $tasks->moveToProject('t1', 'p2');
    $tasks->moveToSection('t1', 's2');
    $tasks->moveUnder('t1', 't0');

    expect(array_map(fn (RequestInterface $r): string => (string) $r->getBody(), $http->sent()))
        ->toBe(['{"project_id":"p2"}', '{"section_id":"s2"}', '{"parent_id":"t0"}']);
    $http->assertSentTo('POST', '/api/v1/tasks/t1/move');
});

it('closes, reopens and deletes', function (): void {
    $http = new FakeClient(FakeResponse::json([]), FakeResponse::json([]), FakeResponse::empty(204));
    $tasks = todoist($http)->tasks();

    $tasks->close('t1');
    $tasks->reopen('t1');
    $tasks->delete('t1');

    $http->assertSentInOrder(['POST /api/v1/tasks/t1/close', 'POST /api/v1/tasks/t1/reopen', 'DELETE /api/v1/tasks/t1']);
});

it('encodes task ids in paths', function (): void {
    $http = new FakeClient(FakeResponse::json(taskJson()));

    todoist($http)->tasks()->get('../../user');

    expect($http->last()?->getUri()->getPath())->toBe('/api/v1/tasks/..%2F..%2Fuser');
});
