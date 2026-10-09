<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomUnit;
use Illuminate\Database\Seeder;

class RoomUnitSeeder extends Seeder
{
    public function run(): void
    {
        $photo = fn (string $id): string => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=1600&q=80";

        // Two or three specific, numbered rooms per type. Most lean on the
        // type's own photos/amenities (an empty array here means exactly
        // that — see RoomUnitResource's fallback); one per type gets its
        // own distinct gallery, to show the override actually works.
        $units = [
            'standard-double-room' => [
                ['number' => '101', 'has_balcony' => false, 'images' => [], 'amenities' => []],
                ['number' => '102', 'has_balcony' => true, 'images' => [$photo('photo-1611892440504-42a792e24d32'), $photo('photo-1522708323590-d24dbb6b0267')], 'amenities' => []],
            ],
            'twin-beds-standard-room' => [
                ['number' => '103', 'has_balcony' => false, 'images' => [], 'amenities' => []],
                ['number' => '104', 'has_balcony' => true, 'images' => [$photo('photo-1590490360182-c33d57733427')], 'amenities' => []],
            ],
            'extended-triple-room' => [
                ['number' => '201', 'has_balcony' => false, 'images' => [], 'amenities' => []],
                ['number' => '202', 'has_balcony' => true, 'images' => [$photo('photo-1582719478250-c89cae4dc85b'), $photo('photo-1618773928121-c32242e63f39')], 'amenities' => []],
            ],
            'family-two-room-triple' => [
                ['number' => '301', 'has_balcony' => true, 'images' => [], 'amenities' => []],
                ['number' => '302', 'has_balcony' => true, 'images' => [$photo('photo-1631049307264-da0ec9d70304')], 'amenities' => []],
                ['number' => '303', 'has_balcony' => false, 'images' => [], 'amenities' => []],
            ],
            'luxury-two-room-triple' => [
                ['number' => '401', 'has_balcony' => true, 'images' => [$photo('photo-1522708323590-d24dbb6b0267'), $photo('photo-1631049307264-da0ec9d70304')], 'amenities' => []],
                ['number' => '402', 'has_balcony' => true, 'images' => [], 'amenities' => []],
            ],
        ];

        foreach ($units as $roomSlug => $roomUnits) {
            $room = Room::query()->where('slug', $roomSlug)->first();

            if ($room === null) {
                continue;
            }

            foreach ($roomUnits as $index => $unit) {
                RoomUnit::updateOrCreate(
                    ['room_id' => $room->id, 'number' => $unit['number']],
                    [
                        'images' => $unit['images'],
                        'amenities' => $unit['amenities'],
                        'has_balcony' => $unit['has_balcony'],
                        'sort_order' => $index,
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
