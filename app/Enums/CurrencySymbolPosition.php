<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where a currency's symbol sits relative to the number. Conventions differ:
 * "$100" (before) versus "100 ₴" (after, with a space).
 */
enum CurrencySymbolPosition: string implements HasLabel
{
    case Before = 'before';
    case After = 'after';

    public function getLabel(): string
    {
        return match ($this) {
            self::Before => __('Before the number (e.g. $100)'),
            self::After => __('After the number (e.g. 100 ₴)'),
        };
    }
}
