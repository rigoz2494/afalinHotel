<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCallbackRequestRequest;
use App\Services\CallbackRequestService;
use Illuminate\Http\JsonResponse;

class CallbackRequestController extends Controller
{
    public function store(StoreCallbackRequestRequest $request, CallbackRequestService $callbacks): JsonResponse
    {
        $callbacks->create($request->validated(), $request->ip());

        return response()->json(['message' => 'Thank you! We will call you back shortly.'], 201);
    }
}
