# PHP SDK for Todoist

A PHP client for [Todoist's API v1](https://developer.todoist.com/api/v1), built on [sdk-toolbox](https://github.com/JustSteveKing/sdk-toolbox).

> This package is not created by, affiliated with, or supported by Todoist.

It's early. Tasks are covered today; projects, sections, labels, comments, OAuth and webhooks are on the way.

## Installing

```bash
composer require juststeveking/todoist-sdk
```

You'll need PHP 8.3 or later and a PSR-18 HTTP client. If you don't already have one, `symfony/http-client` and `nyholm/psr7` will do.

## Getting started

Grab a personal API token from Todoist's integration settings, then:

```php
use JustSteveKing\TodoistSdk\Todoist;
use JustSteveKing\TodoistSdk\Inputs\NewTask;

$todoist = new Todoist(getenv('TODOIST_TOKEN'));

$task = $todoist->tasks()->create(new NewTask('Buy milk', dueString: 'tomorrow at 9am', priority: 4));

echo $task->id;
```

Every task comes back as a typed `Task`, with its due date, duration and deadline as their own objects.

## Tasks

```php
use JustSteveKing\TodoistSdk\Inputs\TaskChanges;
use JustSteveKing\TodoistSdk\Requests\Tasks\ListTasks;

// Every task in a project. Pages are fetched as you iterate.
foreach ($todoist->tasks()->all(new ListTasks(projectId: '6XGgm6PHrGgMpCFX')) as $task) {
    echo $task->content, PHP_EOL;
}

$task = $todoist->tasks()->get('6XGgmFVcrG5RRjVr');

// Only what you set is sent. withoutDeadline() clears it; leaving it out leaves it alone.
$todoist->tasks()->update($task->id, TaskChanges::new()->content('Buy oat milk')->withoutDeadline());

$todoist->tasks()->moveToProject($task->id, '6XGgm6PHrGgMpCFX');
$todoist->tasks()->close($task->id);
$todoist->tasks()->reopen($task->id);
$todoist->tasks()->delete($task->id);
```

## When things go wrong

Failures throw sdk-toolbox's status exceptions, so a missing task is a `NotFoundException` and a bad token is an `UnauthorizedException`. Todoist's own error details are one call away:

```php
use JustSteveKing\SdkToolbox\Errors\NotFoundException;
use JustSteveKing\TodoistSdk\Errors\TodoistError;

try {
    $todoist->tasks()->get('missing');
} catch (NotFoundException $e) {
    $error = TodoistError::of($e);

    $error?->tag;     // "NOT_FOUND"
    $error?->eventId; // quote this if you contact Todoist support
}
```

Reads are retried when Todoist is briefly unavailable, waiting as long as `Retry-After` asks. Creating a task is never retried after an ambiguous failure, because it may already have landed. Your token is redacted from every exception.

## Testing your code

The client takes any PSR-18 client, so sdk-toolbox's `FakeClient` works for tests:

```php
use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeResponse;

$http = new FakeClient(FakeResponse::json(['id' => '...', /* ... */]));
$todoist = new Todoist('test-token', $http);
```

## Built against the spec

The models are checked against Todoist's own OpenAPI spec, kept in `spec/`. A field the API has and the SDK lacks fails the test suite.

## Licence

MIT. See [LICENSE.md](LICENSE.md).
