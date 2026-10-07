<?php

namespace Database\Seeders;

use App\Models\PricingPeriod;
use App\Models\Room;
use App\Services\TranslationService;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(TranslationService $translator): void
    {
        // Several rooms share the same amenity labels (e.g. "Wardrobe"), so
        // each distinct one is only ever sent to the translation service once.
        $translatedFurniture = [];
        $translateFurnitureList = function (array $furniture) use ($translator, &$translatedFurniture): array {
            return array_map(function (string $item) use ($translator, &$translatedFurniture): string {
                return $translatedFurniture[$item] ??= $translator->translateAuto($item)['ru'];
            }, $furniture);
        };

        // period order => PricingPeriod id, e.g. [1 => 3, 2 => 4, ...]
        $periodIds = PricingPeriod::query()->ordered()->pluck('id', 'sort_order');

        $photo = fn (string $id): string => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=1600&q=80";

        // Room index => [period sort_order => promo discount %]. Each room's
        // regular rate per month already comes from its base_price and the
        // period's modifier_percentage; this only adds the occasional extra
        // promotional discount on top, for a specific room and month.

        $galleries = [
            [$photo('photo-1611892440504-42a792e24d32'), $photo('photo-1618773928121-c32242e63f39'), $photo('photo-1522708323590-d24dbb6b0267')],
            [$photo('photo-1590490360182-c33d57733427'), $photo('photo-1631049307264-da0ec9d70304'), $photo('photo-1611892440504-42a792e24d32')],
            [$photo('photo-1582719478250-c89cae4dc85b'), $photo('photo-1618773928121-c32242e63f39'), $photo('photo-1590490360182-c33d57733427')],
            [$photo('photo-1631049307264-da0ec9d70304'), $photo('photo-1582719478250-c89cae4dc85b'), $photo('photo-1522708323590-d24dbb6b0267')],
        ];

        $discounts = [1 => [1 => 10], 2 => [1 => 15, 2 => 5], 3 => [1 => 10, 2 => 10]];

        $rooms = [
            ['Standard Room', 'A calm, light-filled room for solo travellers and couples.', 2, 'Queen', ['Bedside table', 'Wardrobe', 'Clothes rack'], 90],
            ['Deluxe Room', 'Generous space with a reading corner and city views.', 2, 'King', ['Bedside table', 'Wardrobe', 'Armchair', 'Clothes rack'], 140],
            ['Family Suite', 'Two connected rooms designed for families of up to four.', 4, 'Twin + Double', ['Bedside table', 'Wardrobe', 'Armchair', 'Clothes rack'], 210],
            ['Presidential Suite', 'Our signature suite with a private lounge and panoramic terrace.', 3, 'King', ['Bedside table', 'Wardrobe', 'Armchair', 'Clothes rack'], 380],
        ];

        foreach ($rooms as $index => [$name, $description, $capacity, $bed, $furniture, $basePrice]) {
            $room = Room::updateOrCreate(['slug' => str($name)->slug()->toString()], [
                'name' => $name,
                'description' => $description,
                'capacity' => $capacity,
                'bed_type' => $bed,
                'has_tv' => true,
                'has_air_conditioning' => true,
                'furniture' => $furniture,
                'furniture_ru' => $translateFurnitureList($furniture),
                'images' => $galleries[$index],
                'base_price' => $basePrice,
                'sort_order' => $index,
                'is_active' => true,
            ]);

            foreach ($discounts[$index] ?? [] as $order => $discountPercentage) {
                if (! isset($periodIds[$order])) {
                    continue;
                }

                $room->discountOverrides()->updateOrCreate(
                    ['pricing_period_id' => $periodIds[$order]],
                    ['discount_percentage' => $discountPercentage],
                );
            }
        }
    }
}
