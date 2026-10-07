<?php

namespace Database\Seeders;

use App\Models\PricingPeriod;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // sort_order => PricingPeriod id, e.g. [1 => 3, 2 => 4, ...].
        $periodIds = PricingPeriod::query()->ordered()->pluck('id', 'sort_order');

        $photo = fn (string $id): string => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=1600&q=80";

        $galleries = [
            [$photo('photo-1611892440504-42a792e24d32'), $photo('photo-1618773928121-c32242e63f39'), $photo('photo-1522708323590-d24dbb6b0267')],
            [$photo('photo-1590490360182-c33d57733427'), $photo('photo-1631049307264-da0ec9d70304'), $photo('photo-1611892440504-42a792e24d32')],
            [$photo('photo-1582719478250-c89cae4dc85b'), $photo('photo-1618773928121-c32242e63f39'), $photo('photo-1590490360182-c33d57733427')],
            [$photo('photo-1631049307264-da0ec9d70304'), $photo('photo-1582719478250-c89cae4dc85b'), $photo('photo-1522708323590-d24dbb6b0267')],
            [$photo('photo-1522708323590-d24dbb6b0267'), $photo('photo-1611892440504-42a792e24d32'), $photo('photo-1631049307264-da0ec9d70304')],
            [$photo('photo-1618773928121-c32242e63f39'), $photo('photo-1590490360182-c33d57733427')],
        ];

        // The six real room categories. Each one's rate for period 1 (the
        // baseline season) is its own base_price; periods 2-4 come from
        // PricingPeriodSeeder's modifier where that lands on the real rate
        // exactly (true for room 1 only — see that seeder's own comment) and
        // from an explicit `overrides` price otherwise.
        $rooms = [
            [
                'slug' => 'standard-double-room',
                'name' => ['en' => 'Standard Double Room', 'ru' => 'Стандарт однокомнатный 2-х местный'],
                'description' => [
                    'en' => 'A cozy one-room suite for two guests, equipped with everything needed for a comfortable stay by the sea.',
                    'ru' => 'Уютный однокомнатный номер для двух гостей. Оборудован всем необходимым для комфортного отдыха у моря.',
                ],
                'capacity' => 2,
                'bed_type' => 'Double bed',
                'amenities' => ['double_bed', 'table', 'nightstand', 'chairs', 'wardrobe', 'tv', 'ac', 'fridge', 'safe_box'],
                'base_price' => 4500,
                'overrides' => [],
            ],
            [
                'slug' => 'twin-beds-standard-room',
                'name' => ['en' => 'Twin Beds Standard Room', 'ru' => 'Стандарт однокомнатный 2-х местный (раздельные кровати)'],
                'description' => [
                    'en' => 'A comfortable one-room suite with separate beds, ideal for friends or colleagues.',
                    'ru' => 'Комфортабельный однокомнатный номер с раздельными спальными местами, идеальный для друзей или коллег.',
                ],
                'capacity' => 2,
                'bed_type' => 'Twin beds',
                'amenities' => ['twin_beds', 'table', 'nightstand', 'chairs', 'wardrobe', 'tv', 'ac', 'fridge', 'safe_box'],
                'base_price' => 4700,
                'overrides' => [2 => '5700', 3 => '6200', 4 => '5700'],
            ],
            [
                'slug' => 'extended-triple-room',
                'name' => ['en' => 'Extended Triple Room', 'ru' => 'Расширенный однокомнатный 3-х местный'],
                'description' => [
                    'en' => 'A spacious one-room suite with an extra bed, perfect for a small family.',
                    'ru' => 'Просторный однокомнатный номер с дополнительным спальным местом, отлично подходящий для небольшой семьи.',
                ],
                'capacity' => 3,
                'bed_type' => 'Double bed + sofa',
                'amenities' => ['double_bed', 'sofa', 'table', 'nightstand', 'chairs', 'wardrobe', 'tv', 'ac', 'fridge', 'safe_box'],
                'base_price' => 5500,
                'overrides' => [2 => '6500', 3 => '7500', 4 => '6500'],
            ],
            [
                'slug' => 'family-two-room-triple',
                'name' => ['en' => 'Family Two-Room Triple', 'ru' => 'Семейный двухкомнатный 3-х местный'],
                'description' => [
                    'en' => 'A two-room suite creating the perfect conditions for a private, peaceful family stay.',
                    'ru' => 'Двухкомнатный номер, создающий идеальные условия для уединенного и спокойного семейного отдыха.',
                ],
                'capacity' => 3,
                'bed_type' => 'Double bed + sofa',
                'amenities' => ['double_bed', 'table', 'nightstand', 'chairs', 'sofa', 'wardrobe', 'hanger', 'tv', 'ac', 'fridge', 'safe_box'],
                'base_price' => 6500,
                'overrides' => [2 => '8500', 3 => '9500', 4 => '8500'],
            ],
            [
                'slug' => 'luxury-two-room-triple',
                'name' => ['en' => 'Luxury Two-Room Triple', 'ru' => 'Люкс двухкомнатный 3-х местный'],
                'description' => [
                    'en' => 'A premium two-room suite of enhanced comfort, with elegant furniture for an unforgettable stay.',
                    'ru' => 'Премиальный двухкомнатный люкс повышенной комфортности с изысканной мебелью для незабываемого отдыха.',
                ],
                'capacity' => 3,
                'bed_type' => 'Double bed + sofa',
                'amenities' => ['double_bed', 'table', 'nightstand', 'chairs', 'armchair', 'sofa', 'wardrobe', 'hanger', 'tv', 'ac', 'fridge', 'safe_box'],
                'base_price' => 7500,
                'overrides' => [2 => '9500', 3 => '11500', 4 => '9500'],
            ],
            [
                // Not a bookable room in the usual sense — a supplemental
                // line item for an extra bed, priced per guest (child or
                // adult), shown as its own row in the pricing table. Every
                // period is overridden with the literal "child/adult" text,
                // since there's no single number a season modifier could
                // compute for it.
                'slug' => 'extra-bed-space',
                'name' => ['en' => 'Extra Bed Space', 'ru' => 'Дополнительное место'],
                'description' => [
                    'en' => 'An extra bed added to a room, for a child (600-1000) or an adult guest (900-1500), depending on the season.',
                    'ru' => 'Оформление дополнительного спального места в номере для ребенка (600-1000) или взрослого гостя (900-1500).',
                ],
                'capacity' => 1,
                'bed_type' => 'Extra bed',
                'amenities' => [],
                'base_price' => 600,
                'overrides' => [1 => '600/900', 2 => '900/1300', 3 => '1000/1500', 4 => '900/1300'],
            ],
        ];

        foreach ($rooms as $index => $data) {
            $room = Room::updateOrCreate(['slug' => $data['slug']], [
                'name' => $data['name'],
                'description' => $data['description'],
                'capacity' => $data['capacity'],
                'bed_type' => $data['bed_type'],
                'amenities' => $data['amenities'],
                'images' => $galleries[$index],
                'base_price' => $data['base_price'],
                'sort_order' => $index,
                'is_active' => true,
            ]);

            foreach ($data['overrides'] as $order => $priceOverride) {
                if (! isset($periodIds[$order])) {
                    continue;
                }

                $room->discountOverrides()->updateOrCreate(
                    ['pricing_period_id' => $periodIds[$order]],
                    ['price_override' => $priceOverride],
                );
            }
        }
    }
}
