# PHP SDK for Todoist

A PHP client for [Todoist's API v1](https://developer.todoist.com/api/v1), built on [sdk-toolbox](https://github.com/JustSteveKing/sdk-toolbox).

> This package is not created by, affiliated with, or supported by Todoist.

It's early. Tasks, projects, sections, labels, comments, uploads, the user and OAuth are covered today; webhooks are on the way.

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

## Projects, sections, labels and comments

Each works the same way as tasks: `all()` pages as you iterate, `create()` takes a `New...` input, and `update()` takes a `...Changes` builder that sends only what you set.

```php
use JustSteveKing\TodoistSdk\Inputs\Color;
use JustSteveKing\TodoistSdk\Inputs\NewComment;
use JustSteveKing\TodoistSdk\Inputs\NewProject;
use JustSteveKing\TodoistSdk\Inputs\NewSection;
use JustSteveKing\TodoistSdk\Inputs\ProjectChanges;
use JustSteveKing\TodoistSdk\Inputs\ViewStyle;

$project = $todoist->projects()->create(new NewProject('Kitchen refit', color: Color::Teal, viewStyle: ViewStyle::Board));
$todoist->sections()->create(new NewSection('Quotes', $project->id));

$todoist->projects()->update($project->id, ProjectChanges::new()->defaultIcon());
$todoist->projects()->archive($project->id);

foreach ($todoist->labels()->search('wait') as $label) {
    echo $label->name, PHP_EOL;
}

$todoist->comments()->create(NewComment::onTask('6XGgmFVcrG5RRjVr', 'Need one bottle of milk'));
```

Personal and workspace projects come back as the same `Project`. The fields only a workspace project has are null on a personal one, so `$project->workspaceId !== null` tells you which you've got.

Shared labels are only names on tasks shared with you, so they have their own methods: `labels()->shared()`, `renameShared()` and `removeShared()`.

## Uploads and the user

```php
use JustSteveKing\TodoistSdk\Inputs\NewComment;
use JustSteveKing\TodoistSdk\Inputs\Upload;

$file = $todoist->uploads()->upload(Upload::fromPath('/tmp/quote.pdf', 'application/pdf'));
$todoist->comments()->create(NewComment::onTask($task->id, 'The quote', attachment: $file));

$me = $todoist->user();
echo $me->fullName, ' (', $me->tzInfo->timezone, ')';
```

`User` leaves out the user's own API token, which Todoist sends back in `/user`, so it can't end up in a dump or a log.

## Signing users in with OAuth

A personal token is fine for your own account. For other people's, register an app in Todoist's App Management Console and use `TodoistOAuth`:

```php
use JustSteveKing\TodoistSdk\OAuth\Scope;
use JustSteveKing\TodoistSdk\OAuth\TodoistOAuth;

$oauth = new TodoistOAuth($clientId, $clientSecret);

// Send the user to Todoist, keeping what start() returns in their session.
$started = $oauth->start('https://app.test/oauth/todoist', Scope::DataReadWrite);
$session->put('todoist_oauth', $started);

return redirect($started->url);
```

When they come back:

```php
$token = $oauth->exchange('https://app.test/oauth/todoist', $session->pull('todoist_oauth'), $request->query('code'), $request->query('state'));

$store->put("todoist:{$user->id}", $token);
```

From then on, give the client an authenticator instead of a token, and it keeps the token fresh:

```php
$todoist = new Todoist($oauth->refreshing("todoist:{$user->id}", $store));
```

`$store` is any sdk-toolbox `TokenStore`: `new Psr16TokenStore($cache)` for a web app. PKCE and the `state` check happen on every flow. Leave the secret out for a public client, such as a CLI.

Todoist rotates refresh tokens, and treats a spent one sent again after 60 seconds as a replay, revoking every token the user gave your app. `refreshing()` never sends a spent one: if a refresh reply comes back without a new refresh token, the user signs in again when that token expires.

`$oauth->revoke($accessToken)` revokes a token, and `TodoistOAuth::register()` registers a client at runtime (RFC 7591) for apps that can't use the console, such as MCP servers.

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
