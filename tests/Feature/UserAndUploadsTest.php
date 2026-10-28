<?php

declare(strict_types=1);

use JustSteveKing\SdkToolbox\Errors\InvalidRequestException;
use JustSteveKing\SdkToolbox\Testing\FakeClient;
use JustSteveKing\SdkToolbox\Testing\FakeResponse;
use JustSteveKing\TodoistSdk\Data\StatusEffect;
use JustSteveKing\TodoistSdk\Inputs\Upload;

it('maps the user', function (): void {
    $http = new FakeClient(FakeResponse::json(example('user')));

    $user = todoist($http)->user();

    expect($user->email)->toBe('user@example.com')
        ->and($user->tzInfo->timezone)->toBe('Europe/Madrid')
        ->and($user->karma)->toBe(1024.0)
        ->and($user->daysOff)->toBe([6, 7])
        ->and($user->status)->toBeNull();
    $http->assertSentTo('GET', '/api/v1/user');
});

it('leaves the user\'s own API token out of the model', function (): void {
    $user = todoist(new FakeClient(FakeResponse::json(example('user', ['token' => 'their-api-token']))))->user();

    expect(print_r($user, true))->not->toContain('their-api-token')
        ->and(property_exists($user, 'token'))->toBeFalse()
        ->and(property_exists($user, 'websocketUrl'))->toBeFalse();
});

it('accepts daylight saving as 0 or 1, as older Todoist APIs sent it', function (): void {
    $user = todoist(new FakeClient(FakeResponse::json(example('user', ['tz_info' => ['timezone' => 'Europe/London', 'gmt_string' => '+00:00', 'is_dst' => 0, 'hours' => 0, 'minutes' => 0]]))))->user();

    expect($user->tzInfo->isDst)->toBe(0);
});

it('maps a status and what it pauses', function (): void {
    $user = todoist(new FakeClient(FakeResponse::json(example('user', [
        'status' => ['message' => 'On holiday', 'emoji' => '⛱️', 'active_until' => '2026-09-08T15:00:00Z', 'effects' => ['pause_activity' => []]],
    ]))))->user();

    expect($user->status?->message)->toBe('On holiday')
        ->and($user->status?->effects->pauseActivity)->toEqual(new StatusEffect('all'))
        ->and($user->status?->effects->pauseNotifications)->toBeNull();
});

it('uploads a file as multipart, and gets back an attachment for a comment', function (): void {
    $http = new FakeClient(FakeResponse::json([
        'file_url' => 'https://files.todoist.com/abc123/by/1234567/as/quote.pdf',
        'file_name' => 'quote.pdf',
        'file_size' => 9,
        'file_type' => 'application/pdf',
        'resource_type' => 'file',
        'image' => null,
        'image_width' => null,
        'image_height' => null,
        'upload_state' => 'completed',
    ]));

    $attachment = todoist($http)->uploads()->upload(Upload::fromContents('%PDF-1.7', 'quote.pdf', 'application/pdf', projectId: 'p1'));

    $request = $http->last();
    $body = (string) $request?->getBody();

    expect($attachment->fileUrl)->toBe('https://files.todoist.com/abc123/by/1234567/as/quote.pdf')
        ->and($attachment->uploadState)->toBe('completed')
        ->and($request?->getHeaderLine('Content-Type'))->toStartWith('multipart/form-data; boundary=')
        ->and($body)->toContain('Content-Disposition: form-data; name="file"; filename="quote.pdf"')
        ->and($body)->toContain("Content-Type: application/pdf\r\n\r\n%PDF-1.7")
        ->and($body)->toContain("name=\"file_name\"\r\n\r\nquote.pdf")
        ->and($body)->toContain("name=\"project_id\"\r\n\r\np1");
});

it('uploads from a path, streaming the file', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'todoist');
    file_put_contents((string) $path, 'shopping list');
    $http = new FakeClient(FakeResponse::json(['file_url' => 'https://files.todoist.com/x', 'file_name' => 'list.txt', 'file_size' => 13, 'file_type' => 'text/plain', 'resource_type' => 'file']));

    todoist($http)->uploads()->upload(Upload::fromPath((string) $path, 'text/plain', 'list.txt'));
    unlink((string) $path);

    expect((string) $http->last()?->getBody())->toContain("filename=\"list.txt\"\r\nContent-Type: text/plain\r\n\r\nshopping list");
});

it('refuses a path it cannot read', function (): void {
    expect(fn () => Upload::fromPath('/nowhere/missing.pdf'))->toThrow(InvalidRequestException::class);
});

it('deletes an upload by its URL', function (): void {
    $http = new FakeClient(FakeResponse::text('"ok"', headers: ['Content-Type' => 'application/json']));

    todoist($http)->uploads()->delete('https://files.todoist.com/abc 123/file.pdf');

    $http->assertSentTo('DELETE', '/api/v1/uploads');
    expect(urldecode($http->last()?->getUri()->getQuery() ?? ''))->toBe('file_url=https://files.todoist.com/abc 123/file.pdf');
});
