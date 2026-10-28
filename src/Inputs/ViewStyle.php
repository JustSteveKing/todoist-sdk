<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

enum ViewStyle: string
{
    case List = 'list';
    case Board = 'board';
    case Calendar = 'calendar';
}
