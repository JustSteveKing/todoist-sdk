<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Errors;

use JsonException;
use JustSteveKing\SdkToolbox\Errors\ProblemDetails;
use JustSteveKing\SdkToolbox\Errors\StatusExceptions;
use JustSteveKing\SdkToolbox\Http\Response;

/**
 * Todoist's errors, read into the toolbox's exceptions.
 *
 * Todoist doesn't send RFC 9457 problem details. Its errors look like:
 *
 *     {"error": "Unauthorized", "error_code": 477, "error_tag": "UNAUTHORIZED",
 *      "error_extra": {"event_id": "78c3…", "retry_after": 5}, "http_code": 401}
 *
 * So the status still picks the exception (a 404 is a NotFoundException,
 * and so on), through sdk-toolbox's StatusExceptions, and this reads
 * Todoist's fields into the problem every exception carries, where
 * TodoistError::of() reads them back.
 *
 * @internal
 */
final readonly class TodoistErrors
{
    public static function mapper(): StatusExceptions
    {
        return new StatusExceptions(problems: self::problem(...));
    }

    public static function problem(Response $response): ?ProblemDetails
    {
        try {
            $body = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (! is_array($body) || ! is_string($body['error'] ?? null)) {
            return null;
        }

        $extra = is_array($body['error_extra'] ?? null) ? $body['error_extra'] : [];

        return new ProblemDetails(
            title: $body['error'],
            status: $response->status(),
            extensions: [
                'error_code' => is_int($body['error_code'] ?? null) ? $body['error_code'] : null,
                'error_tag' => is_string($body['error_tag'] ?? null) ? $body['error_tag'] : null,
                'event_id' => is_string($extra['event_id'] ?? null) ? $extra['event_id'] : null,
                'error_extra' => $extra,
            ],
        );
    }
}
