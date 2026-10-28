<?php

declare(strict_types=1);

namespace JustSteveKing\TodoistSdk\Inputs;

/**
 * The colours Todoist accepts for projects and labels.
 */
enum Color: string
{
    case BerryRed = 'berry_red';
    case Red = 'red';
    case Orange = 'orange';
    case Yellow = 'yellow';
    case OliveGreen = 'olive_green';
    case LimeGreen = 'lime_green';
    case Green = 'green';
    case MintGreen = 'mint_green';
    case Teal = 'teal';
    case SkyBlue = 'sky_blue';
    case LightBlue = 'light_blue';
    case Blue = 'blue';
    case Grape = 'grape';
    case Violet = 'violet';
    case Lavender = 'lavender';
    case Magenta = 'magenta';
    case Salmon = 'salmon';
    case Charcoal = 'charcoal';
    case Grey = 'grey';
    case Taupe = 'taupe';
}
