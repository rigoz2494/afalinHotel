<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Services\CurrencyService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;

class PricingController extends Controller
{
    public function index(PricingService $pricing, CurrencyService $currencies): JsonResponse
    {
        $table = $pricing->table();

        return response()->json([
            // Rows are already plain, computed arrays (see PricingService),
            // not Eloquent models, so there is no resource to wrap them with.
            'data' => $table['rows'],
            'columns' => $table['columns'],
            'currencies' => CurrencyResource::collection($currencies->selectable())->resolve(),
        ]);
    }
}
