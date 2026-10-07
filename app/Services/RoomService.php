<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

class RoomService
{
    /**
     * @return Collection<int, Room>
     */
    public function listActive(): Collection
    {
        return Room::query()->active()->ordered()->get();
    }
}
