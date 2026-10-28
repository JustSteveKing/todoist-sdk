<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Requests\User;

use JustSteveKing\SdkToolbox\Http\Method;
use JustSteveKing\SdkToolbox\Http\Response;
use JustSteveKing\SdkToolbox\Requests\Request;
use JustSteveKing\TodoistSdk\Data\User;

/**
 * The user the token belongs to.
 *
 * @implements Request<User>
 */
final readonly class GetUser implements Request
{
    public function method(): Method
    {
        return Method::Get;
    }

    public function path(): string
    {
        return '/user';
    }

    public function result(Response $response): User
    {
        return $response->map(User::class);
    }
}
