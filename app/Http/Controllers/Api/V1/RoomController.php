<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomResource;
use App\Services\RoomService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoomController extends Controller
{
    public function index(RoomService $rooms): AnonymousResourceCollection
    {
        return RoomResource::collection($rooms->listActive());
    }
}
