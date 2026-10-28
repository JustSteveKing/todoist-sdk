<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Data;

/**
 * A file on a comment. Only the URL is certain: an attachment posted by
 * another client may carry no more than that and a resource type, while
 * one from Todoist's own upload has the size, type and image details.
 */
final readonly class FileAttachment
{
    /**
     * @param  'pending'|'completed'|null  $uploadState
     */
    public function __construct(
        public string $fileUrl,
        public ?string $fileName = null,
        public ?string $fileType = null,
        public ?int $fileSize = null,
        public ?string $resourceType = null,
        public ?string $image = null,
        public ?int $imageWidth = null,
        public ?int $imageHeight = null,
        public ?string $uploadState = null,
    ) {}

    /**
     * The attachment as Todoist takes it on a new comment.
     *
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return array_filter([
            'file_url' => $this->fileUrl,
            'file_name' => $this->fileName,
            'file_type' => $this->fileType,
            'file_size' => $this->fileSize,
            'resource_type' => $this->resourceType,
            'image' => $this->image,
            'image_width' => $this->imageWidth,
            'image_height' => $this->imageHeight,
            'upload_state' => $this->uploadState,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
