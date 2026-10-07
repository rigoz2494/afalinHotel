<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelSettingsResource;
use App\Services\HotelSettingService;

class HotelSettingController extends Controller
{
    public function show(HotelSettingService $settings): HotelSettingsResource
    {
        return new HotelSettingsResource($settings->all());
    }
}
