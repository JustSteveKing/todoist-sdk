<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Errors\NotFoundException;
use JustSteveKing\SdkToolbox\Errors\ServerException;
use JustSteveKing\SdkToolbox\Errors\UnauthorizedException;
use JustSteveKing\SdkToolbox\Retries\RetryPolicy;
use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeResponse;
use JustSteveKing\SdkToolbox\Testing\FakeTime;
use JustSteveKing\TodoistSdk\Errors\TodoistError;

/**
 * The error shape is the one Todoist returns live, read from an
 * unauthenticated request to /api/v1/tasks on 2026-10-28.
 */
function todoistError(string $error, int $code, string $tag, int $status, ?int $retryAfter = null): Psr\Http\Message\ResponseInterface
{
    return FakeResponse::json(
        ['error' => $error, 'error_code' => $code, 'error_extra' => ['event_id' => '78c3ca3b044a4861aea0538dad9dcea0', 'retry_after' => $retryAfter ?? 5], 'error_tag' => $tag, 'http_code' => $status],
        $status,
        $retryAfter === null ? [] : ['Retry-After' => (string) $retryAfter],
    );
}

it('throws the status exception with Todoist\'s own details', function (): void {
    $http = new FakeClient(todoistError('Task not found', 478, 'NOT_FOUND', 404));

    try {
        todoist($http)->tasks()->get('missing');
        $this->fail('Expected a NotFoundException');
    } catch (NotFoundException $e) {
        $error = TodoistError::of($e);

        expect($error?->tag)->toBe('NOT_FOUND')
            ->and($error?->code)->toBe(478)
            ->and($error?->eventId)->toBe('78c3ca3b044a4861aea0538dad9dcea0')
            ->and($e->getMessage())->toBe('GET /api/v1/tasks/missing returned 404 Not Found: Task not found');
    }
});

it('never retries a rejected token, though Todoist sends Retry-After with it', function (): void {
    $http = new FakeClient(todoistError('Unauthorized', 477, 'UNAUTHORIZED', 401, retryAfter: 5));

    expect(fn () => todoist($http, retry: RetryPolicy::standard())->tasks()->get('t1'))->toThrow(UnauthorizedException::class);
    $http->assertSentCount(1);
});

it('retries a read when Todoist is briefly unavailable, waiting as asked', function (): void {
    $time = new FakeTime;
    $http = new FakeClient(todoistError('Service unavailable', 500, 'SERVICE_UNAVAILABLE', 503, retryAfter: 2), FakeResponse::json(taskJson()));

    $task = todoist($http, $time, RetryPolicy::standard())->tasks()->get('t1');

    expect($task->id)->toBe('6XGgmFVcrG5RRjVr')
        ->and($time->sleeps())->toBe([2.0]);
});

it('does not retry creating a task after a 502, because it may have landed', function (): void {
    $http = new FakeClient(FakeResponse::empty(502));

    expect(fn () => todoist($http, retry: RetryPolicy::standard())->tasks()->create(new JustSteveKing\TodoistSdk\Inputs\NewTask('x')))
        ->toThrow(ServerException::class);
    $http->assertSentCount(1);
});

it('keeps the token out of exceptions', function (): void {
    $http = new FakeClient(todoistError('Forbidden', 403, 'FORBIDDEN', 403));

    try {
        todoist($http)->tasks()->get('t1');
    } catch (Throwable $e) {
        expect(print_r($e, true))->not->toContain('test-token');
    }
});

it('falls back to the plain status exception for a body that is not Todoist\'s', function (): void {
    $http = new FakeClient(FakeResponse::text('<html>Bad gateway</html>', 404));

    try {
        todoist($http)->tasks()->get('t1');
    } catch (NotFoundException $e) {
        expect(TodoistError::of($e))->toBeNull();
    }
});
