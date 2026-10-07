<?php

namespace Tests\Feature;

use App\Filament\Resources\CallbackRequests\CallbackRequestResource;
use App\Filament\Resources\CallbackRequests\Pages\EditCallbackRequest;
use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Resources\Faqs\Pages\EditFaq;
use App\Filament\Resources\RoomPrices\Pages\EditRoomPrice;
use App\Filament\Resources\RoomPrices\RoomPriceResource;
use App\Filament\Resources\Rooms\Pages\EditRoom;
use App\Filament\Resources\Rooms\RoomResource;
use App\Models\CallbackRequest;
use App\Models\Faq;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * After saving an edit, every admin resource should return the user to
 * its index list instead of leaving them on the edit form.
 */
class FilamentEditRedirectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_a_room_redirects_to_the_rooms_list(): void
    {
        $this->actingAs(User::factory()->create());
        $room = Room::factory()->create();

        Livewire::test(EditRoom::class, ['record' => $room->getRouteKey()])
            ->call('save')
            ->assertRedirect(RoomResource::getUrl('index'));
    }

    public function test_saving_a_seasonal_price_redirects_to_the_seasonal_prices_list(): void
    {
        $this->actingAs(User::factory()->create());
        $price = RoomPrice::factory()->for(Room::factory())->create();

        Livewire::test(EditRoomPrice::class, ['record' => $price->getRouteKey()])
            ->call('save')
            ->assertRedirect(RoomPriceResource::getUrl('index'));
    }

    public function test_saving_a_faq_redirects_to_the_faqs_list(): void
    {
        $this->actingAs(User::factory()->create());
        $faq = Faq::factory()->create();

        Livewire::test(EditFaq::class, ['record' => $faq->getRouteKey()])
            ->call('save')
            ->assertRedirect(FaqResource::getUrl('index'));
    }

    public function test_saving_a_booking_redirects_to_the_bookings_list(): void
    {
        $this->actingAs(User::factory()->create());
        $booking = CallbackRequest::factory()->create();

        Livewire::test(EditCallbackRequest::class, ['record' => $booking->getRouteKey()])
            ->call('save')
            ->assertRedirect(CallbackRequestResource::getUrl('index'));
    }
}
