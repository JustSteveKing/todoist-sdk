<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\Labels;

use JustSteveKing\SdkToolbox\Bodies\Body;
use JustSteveKing\SdkToolbox\Bodies\JsonBody;
use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\HasBody;
use JustSteveKing\SdkToolbox\Requests\Request;

/**
 * Renames a shared label on every active task that has it.
 *
 * @implements Request<null>
 */
final readonly class RenameSharedLabel implements HasBody, Request
{
    public function __construct(
        private string $name,
        private string $newName,
    ) {}

    public function method(): Method
    {
        return Method::Post;
    }

    public function path(): string
    {
        return '/labels/shared/rename';
    }

    public function body(): Body
    {
        return new JsonBody(['name' => $this->name, 'new_name' => $this->newName]);
    }

    public function result(Response $response): null
    {
        return null;
    }
}
