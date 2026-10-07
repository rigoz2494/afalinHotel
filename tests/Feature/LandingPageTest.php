<?php

namespace Tests\Feature;

use App\Models\HotelSetting;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_receives_hotel_rooms_and_pricing_props(): void
    {
        HotelSetting::factory()->create(['key' => 'hotel_name', 'value' => ['en' => 'Afalina', 'ru' => 'Афалина']]);
        Room::factory()->count(2)->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Landing')
                ->where('hotel.hotel_name.en', 'Afalina')
                ->where('hotel.hotel_name.ru', 'Афалина')
                ->has('rooms.data', 2)
                ->has('pricing.rows.data', 2)
                ->has('pricing.columns')
                ->has('currencies'));
    }

    public function test_landing_page_serves_uploaded_hero_images_as_public_storage_urls(): void
    {
        HotelSetting::factory()->create(['key' => 'hero_images', 'value' => ['hero/lobby.jpg']]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Landing')
                ->where('hotel.hero_images', [url(Storage::disk('public')->url('hero/lobby.jpg'))]));
    }

    public function test_home_has_no_room_focused_and_no_canonical_room_url(): void
    {
        Room::factory()->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Landing')
                ->where('focusRoomSlug', null)
                ->where('canonicalUrl', route('home')));
    }

    public function test_a_room_has_its_own_clean_indexable_url(): void
    {
        $room = Room::factory()->create(['slug' => 'ocean-view-suite']);

        $this->get(route('rooms.show', $room))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Landing')
                ->where('focusRoomSlug', 'ocean-view-suite')
                ->where('canonicalUrl', route('rooms.show', $room))
                // The room page still carries the full page's data, since it
                // renders the exact same landing experience, just opened to
                // this room, not a stripped-down, room-only view.
                ->has('rooms.data', 1)
                ->has('pricing.rows.data', 1));
    }

    public function test_an_inactive_rooms_url_is_not_reachable(): void
    {
        $room = Room::factory()->inactive()->create(['slug' => 'back-office-room']);

        $this->get(route('rooms.show', $room))->assertNotFound();
    }
}
