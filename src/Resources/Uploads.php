<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Resources;

use JustSteveKing\TodoistSdk\Data\FileAttachment;
use JustSteveKing\TodoistSdk\Inputs\Upload;
use JustSteveKing\TodoistSdk\Requests\Uploads\DeleteUpload;
use JustSteveKing\TodoistSdk\Requests\Uploads\UploadFile;
use JustSteveKing\TodoistSdk\Todoist;

final readonly class Uploads
{
    public function __construct(
        private Todoist $todoist,
    ) {}

    /**
     * Uploads a file; pass what comes back to NewComment's `attachment`.
     */
    public function upload(Upload $upload): FileAttachment
    {
        return $this->todoist->send(new UploadFile($upload));
    }

    public function delete(string $fileUrl): void
    {
        $this->todoist->send(new DeleteUpload($fileUrl));
    }
}
