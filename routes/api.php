<?php

use App\Http\Controllers\Api\V1\CallbackRequestController;
use App\Http\Controllers\Api\V1\HotelSettingController;
use App\Http\Controllers\Api\V1\PricingController;
use App\Http\Controllers\Api\V1\RoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('hotel', [HotelSettingController::class, 'show'])->name('hotel.show');
    Route::get('rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('pricing', [PricingController::class, 'index'])->name('pricing.index');
    Route::post('callback-requests', [CallbackRequestController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('callback-requests.store');
});
