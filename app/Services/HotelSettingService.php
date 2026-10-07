<?php

namespace App\Services;

use App\Models\HotelSetting;

class HotelSettingService
{
    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return HotelSetting::query()->pluck('value', 'key')->all();
    }
}
