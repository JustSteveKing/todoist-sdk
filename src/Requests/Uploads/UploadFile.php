<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Uploads;

use JustSteveKing\SdkToolbox\Bodies\Body;
use JustSteveKing\SdkToolbox\Bodies\Multipart;
use JustSteveKing\SdkToolbox\Bodies\Part;
use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasBody;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\FileAttachment;
use JustSteveKing\TodoistSdk\Inputs\Upload;

/**
 * Uploads a file as multipart/form-data. What comes back is the
 * attachment to put on a comment.
 *
 * @implements Request<FileAttachment>
 */
final readonly class UploadFile implements HasBody, Request
{
    public function __construct(
        private Upload $upload,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return '/uploads';
    }

    public function body(): Body
    {
        $parts = [
            Part::file('file', $this->upload->contents, $this->upload->fileName, $this->upload->contentType),
            Part::field('file_name', $this->upload->fileName),
        ];

        if ($this->upload->projectId !== null) {
            $parts[] = Part::field('project_id', $this->upload->projectId);
        }

        return new Multipart(...$parts);
    }

    public function result(Response $response): FileAttachment
    {
        return $response->map(FileAttachment::class);
    }
}
