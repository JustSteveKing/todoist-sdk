<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Errors\InvalidResponseException;
use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeResponse;
use JustSteveKing\TodoistSdk\Inputs\Color;
use JustSteveKing\TodoistSdk\Inputs\LabelChanges;
use JustSteveKing\TodoistSdk\Inputs\NewLabel;
use Psr\Http\Message\RequestInterface;

it('maps a label', function (): void {
    $label = todoist(new FakeClient(FakeResponse::json(example('label'))))->labels()->get('l1');

    expect($label->name)->toBe('Priority')
        ->and($label->color)->toBe('berry_red')
        ->and($label->order)->toBe(12);
});

it('creates, updates and deletes a personal label', function (): void {
    $http = new FakeClient(FakeResponse::json(example('label')), FakeResponse::json(example('label')), FakeResponse::json([]));
    $labels = todoist($http)->labels();

    $labels->create(new NewLabel('waiting', color: Color::Grey));
    $labels->update('l1', LabelChanges::new()->color(Color::Red)->favorite());
    $labels->delete('l1');

    $http->assertSentInOrder(['POST /api/v1/labels', 'POST /api/v1/labels/l1', 'DELETE /api/v1/labels/l1']);
    expect(array_map(fn (RequestInterface $r): string => (string) $r->getBody(), array_slice($http->sent(), 0, 2)))
        ->toBe(['{"name":"waiting","color":"grey"}', '{"color":"red","is_favorite":true}']);
});

it('pages through shared label names', function (): void {
    $http = new FakeClient(page(['waiting', 'follow-up'], 'c2'), page(['someday']));

    expect(todoist($http)->labels()->shared(omitPersonal: true)->all())->toBe(['waiting', 'follow-up', 'someday'])
        ->and($http->sent()[0]->getUri()->getQuery())->toBe('omit_personal=true');
});

it('refuses shared labels that are not names', function (): void {
    $http = new FakeClient(page([['id' => '1']]));

    expect(fn () => todoist($http)->labels()->shared()->all())->toThrow(InvalidResponseException::class);
});

it('renames and removes a shared label everywhere', function (): void {
    $http = new FakeClient(FakeResponse::json([]), FakeResponse::json([]));
    $labels = todoist($http)->labels();

    $labels->renameShared('waiting', 'follow-up');
    $labels->removeShared('follow-up');

    $http->assertSentInOrder(['POST /api/v1/labels/shared/rename', 'POST /api/v1/labels/shared/remove']);
    expect(array_map(fn (RequestInterface $r): string => (string) $r->getBody(), $http->sent()))
        ->toBe(['{"name":"waiting","new_name":"follow-up"}', '{"name":"follow-up"}']);
});
