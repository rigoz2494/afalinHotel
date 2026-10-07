<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CallbackRequestStatus: string implements HasLabel
{
    case New = 'new';
    case Contacted = 'contacted';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => __('Pending'),
            self::Contacted => __('Processed'),
            self::Closed => __('Closed'),
            self::Cancelled => __('Cancelled'),
        };
    }
}
