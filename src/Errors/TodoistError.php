<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Errors;

use JustSteveKing\SdkToolbox\Errors\HttpException;

/**
 * Todoist's own details of an error, read from any exception the SDK threw.
 *
 *     try {
 *         $todoist->tasks()->get($id);
 *     } catch (NotFoundException $e) {
 *         TodoistError::of($e)?->tag;   // "NOT_FOUND"
 *     }
 *
 * Branch on `tag` or `code`, never on `message`, which is written for people.
 * Quote `eventId` to Todoist's support: it identifies the failed request.
 */
final readonly class TodoistError
{
    /**
     * @param  array<array-key, mixed>  $extra
     */
    public function __construct(
        public string $message,
        public ?string $tag,
        public ?int $code,
        public ?string $eventId,
        public array $extra = [],
    ) {}

    public static function of(HttpException $e): ?self
    {
        $problem = $e->problem;

        if ($problem?->title === null) {
            return null;
        }

        $tag = $problem->extension('error_tag');
        $code = $problem->extension('error_code');
        $eventId = $problem->extension('event_id');
        $extra = $problem->extension('error_extra');

        return new self(
            message: $problem->title,
            tag: is_string($tag) ? $tag : null,
            code: is_int($code) ? $code : null,
            eventId: is_string($eventId) ? $eventId : null,
            extra: is_array($extra) ? $extra : [],
        );
    }
}
