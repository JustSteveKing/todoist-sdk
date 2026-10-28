<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeResponse;
use JustSteveKing\TodoistSdk\Inputs\NewSection;
use JustSteveKing\TodoistSdk\Inputs\SectionChanges;
use Psr\Http\Message\RequestInterface;

it('maps a section', function (): void {
    $section = todoist(new FakeClient(FakeResponse::json(example('section'))))->sections()->get('s1');

    expect($section->name)->toBe('Groceries')
        ->and($section->projectId)->toBe('6XGgm6PHrGgMpCFX')
        ->and($section->sectionOrder)->toBe(1);
});

it('lists a project\'s sections, and searches them', function (): void {
    $http = new FakeClient(page([example('section')]), page([example('section')]));
    $sections = todoist($http)->sections();

    $sections->all('p1')->all();
    $sections->search('groc', 'p1')->all();

    expect(array_map(fn (RequestInterface $r): string => $r->getUri()->getPath().'?'.$r->getUri()->getQuery(), $http->sent()))->toBe([
        '/api/v1/sections?project_id=p1',
        '/api/v1/sections/search?query=groc&project_id=p1',
    ]);
});

it('creates, updates, archives, unarchives and deletes', function (): void {
    $http = new FakeClient(...[...array_map(fn (): mixed => FakeResponse::json(example('section')), range(1, 4)), FakeResponse::json([])]);
    $sections = todoist($http)->sections();

    $sections->create(new NewSection('Groceries', 'p1'));
    $sections->update('s1', SectionChanges::new()->name('Food')->withoutDescription());
    $sections->archive('s1');
    $sections->unarchive('s1');
    $sections->delete('s1');

    $http->assertSentInOrder([
        'POST /api/v1/sections',
        'POST /api/v1/sections/s1',
        'POST /api/v1/sections/s1/archive',
        'POST /api/v1/sections/s1/unarchive',
        'DELETE /api/v1/sections/s1',
    ]);
    expect(array_map(fn (RequestInterface $r): string => (string) $r->getBody(), array_slice($http->sent(), 0, 2)))
        ->toBe(['{"name":"Groceries","project_id":"p1"}', '{"name":"Food","description":null}']);
});
