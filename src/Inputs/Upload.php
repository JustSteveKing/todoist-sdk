<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

use JustSteveKing\SdkToolbox\Errors\InvalidRequestException;
use Psr\Http\Message\StreamInterface;

/**
 * A file to upload, to attach to a comment afterwards. Whether uploads
 * are allowed, and how big, depends on the user's plan.
 *
 *     Upload::fromPath('/tmp/quote.pdf', 'application/pdf')
 */
final readonly class Upload
{
    /**
     * @param  StreamInterface|resource|string  $contents
     */
    private function __construct(
        public mixed $contents,
        public string $fileName,
        public string $contentType,
        public ?string $projectId,
    ) {}

    /**
     * The file is opened now and streamed when the request is sent.
     */
    public static function fromPath(string $path, string $contentType = 'application/octet-stream', ?string $fileName = null, ?string $projectId = null): self
    {
        $handle = is_file($path) && is_readable($path) ? fopen($path, 'rb') : false;

        if ($handle === false) {
            throw new InvalidRequestException("Can't read the file to upload: [{$path}].");
        }

        return new self($handle, $fileName ?? basename($path), $contentType, $projectId);
    }

    /**
     * @param  StreamInterface|resource|string  $contents  the file's contents, or a stream of them
     */
    public static function fromContents(mixed $contents, string $fileName, string $contentType = 'application/octet-stream', ?string $projectId = null): self
    {
        return new self($contents, $fileName, $contentType, $projectId);
    }
}
