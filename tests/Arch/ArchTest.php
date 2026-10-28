<?php

declare(strict_types=1);

arch('strict types everywhere')
    ->expect('JustSteveKing\TodoistSdk')
    ->toUseStrictTypes();

arch('data, inputs and requests are immutable')
    ->expect(['JustSteveKing\TodoistSdk\Data', 'JustSteveKing\TodoistSdk\Inputs', 'JustSteveKing\TodoistSdk\Resources'])
    ->classes()
    ->toBeReadonly();

arch('no debugging left behind')
    ->preset()->php();
