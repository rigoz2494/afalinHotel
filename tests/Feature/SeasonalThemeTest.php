<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageHotelSettings;
use App\Models\HotelSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

class SeasonalThemeTest extends TestCase
{
    public function test_preview_does_not_change_the_published_theme(): void
    {
        $url = URL::temporarySignedRoute('home', now()->addMinutes(15), ['preview_theme' => 'winter']);

        $this->get($url)->assertInertia(fn (Assert $page) => $page->where('hotel.seasonal_theme', 'summer'));

        $this->actingAs(User::factory()->create());
        $this->get($url)->assertInertia(fn (Assert $page) => $page->where('hotel.seasonal_theme', 'winter'));
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('hotel.seasonal_theme', 'summer'));
    }

    use RefreshDatabase;

    public function test_the_saved_theme_reaches_the_public_site(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageHotelSettings::class, ['section' => 'appearance'])
            ->fillForm(['seasonal_theme' => 'spring'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Landing')
            ->where('hotel.seasonal_theme', 'spring'));
    }

    public function test_the_site_uses_summer_without_a_valid_saved_theme(): void
    {
        foreach ([null, 'unknown'] as $value) {
            HotelSetting::query()->updateOrCreate(['key' => 'seasonal_theme'], ['value' => $value]);

            $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Landing')
                ->where('hotel.seasonal_theme', 'summer'));
        }
    }
}
