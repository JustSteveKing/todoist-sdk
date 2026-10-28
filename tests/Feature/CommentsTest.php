<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeResponse;
use JustSteveKing\TodoistSdk\Data\FileAttachment;
use JustSteveKing\TodoistSdk\Inputs\NewComment;
use Psr\Http\Message\RequestInterface;

it('maps comments on a task and on a project', function (): void {
    $http = new FakeClient(FakeResponse::json(example('comment-task')), FakeResponse::json(example('comment-project')));
    $comments = todoist($http)->comments();

    $onTask = $comments->get('c1');
    $onProject = $comments->get('c2');

    expect($onTask->itemId)->toBe('6XGgmFVcrG5RRjVr')
        ->and($onTask->projectId)->toBeNull()
        ->and($onTask->reactions)->toBe(['👍' => ['1234567']])
        ->and($onProject->projectId)->toBe('6XGgm6PHrGgMpCFX')
        ->and($onProject->itemId)->toBeNull();
});

it('maps an attachment however little of it another client sent', function (): void {
    $http = new FakeClient(FakeResponse::json(example('comment-task', [
        'file_attachment' => ['resource_type' => 'file', 'file_url' => 'https://example.com/guide.pdf', 'file_type' => 'application/pdf', 'file_name' => 'guide.pdf'],
    ])));

    expect(todoist($http)->comments()->get('c1')->fileAttachment)
        ->toEqual(new FileAttachment('https://example.com/guide.pdf', 'guide.pdf', 'application/pdf', resourceType: 'file'));
});

it('lists the comments on a task or a project', function (): void {
    $http = new FakeClient(page([example('comment-task')]), page([example('comment-project')]));
    $comments = todoist($http)->comments();

    $comments->onTask('t1')->all();
    $comments->onProject('p1')->all();

    expect(array_map(fn (RequestInterface $r): string => $r->getUri()->getQuery(), $http->sent()))->toBe(['task_id=t1', 'project_id=p1']);
});

it('posts, edits and deletes a comment', function (): void {
    $http = new FakeClient(FakeResponse::json(example('comment-task')), FakeResponse::json(example('comment-project')), FakeResponse::json(example('comment-task')), FakeResponse::json([]));
    $comments = todoist($http)->comments();

    $comments->create(NewComment::onTask('t1', 'Need one bottle of milk', notify: [12345678]));
    $comments->create(NewComment::onProject('p1', 'The plan', attachment: new FileAttachment('https://example.com/plan.pdf', resourceType: 'file')));
    $comments->update('c1', 'Need two bottles of milk');
    $comments->delete('c1');

    $http->assertSentInOrder(['POST /api/v1/comments', 'POST /api/v1/comments', 'POST /api/v1/comments/c1', 'DELETE /api/v1/comments/c1']);
    expect(array_map(fn (RequestInterface $r): mixed => json_decode((string) $r->getBody(), true), array_slice($http->sent(), 0, 3)))->toBe([
        ['content' => 'Need one bottle of milk', 'task_id' => 't1', 'uids_to_notify' => [12345678]],
        ['content' => 'The plan', 'project_id' => 'p1', 'attachment' => ['file_url' => 'https://example.com/plan.pdf', 'resource_type' => 'file']],
        ['content' => 'Need two bottles of milk'],
    ]);
});
