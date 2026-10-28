<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Retries\RetryPolicy;
use JustSteveKing\SdkToolbox\Retries\RetryStrategy;
use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeTime;
use JustSteveKing\TodoistSdk\Todoist;

/**
 * A client on a fake transport. One attempt by default, so a test sees
 * exactly the answer it queued; retry tests pass their own policy.
 */
function todoist(FakeClient $http, ?FakeTime $time = null, ?RetryStrategy $retry = null): Todoist
{
    $time ??= new FakeTime;

    return new Todoist('test-token', $http, retry: $retry ?? RetryPolicy::none(), clock: $time, sleeper: $time);
}

/**
 * The task in Todoist's own OpenAPI examples, with overrides.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function taskJson(array $overrides = []): array
{
    /** @var array<string, mixed> $task */
    $task = json_decode((string) file_get_contents(__DIR__.'/Fixtures/task.json'), true);

    return [...$task, ...$overrides];
}

/**
 * The OpenAPI spec this SDK is built against.
 *
 * @return array<string, mixed>
 */
function spec(): array
{
    /** @var array<string, mixed> */
    return json_decode((string) file_get_contents(dirname(__DIR__).'/spec/todoist-openapi.json'), true);
}
