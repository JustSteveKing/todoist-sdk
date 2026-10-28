<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Uploads;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasQuery;
use JustSteveKing\SdkToolbox\Requests\Request;

/**
 * Deletes an uploaded file, by the URL the upload returned.
 *
 * @implements Request<null>
 */
final readonly class DeleteUpload implements HasQuery, Request
{
    public function __construct(
        private string $fileUrl,
    ) {}

    public function method(): Method
    {
        return Method::Delete;
    }

    public function path(): string
    {
        return '/uploads';
    }

    public function query(): array
    {
        return ['file_url' => $this->fileUrl];
    }

    public function result(Response $response): null
    {
        return null;
    }
}
