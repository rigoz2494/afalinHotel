<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageHotelSettings;
use App\Models\HotelSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The three About Us marketing badges (e.g. "20+ Years") are now admin-
 * editable from Site Settings instead of hardcoded on the page, and —
 * like every other admin text in this app — bilingual.
 */
class BilingualAboutStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_both_languages_for_every_badge_from_the_locale_tabs(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageHotelSettings::class)
            ->fillForm([
                'about_stats.years.label.en' => 'Years',
                'about_stats.years.label.ru' => 'Лет',
                'about_stats.years.value.en' => '25+',
                'about_stats.years.value.ru' => '25+',
                'about_stats.rooms.label.en' => 'Rooms',
                'about_stats.rooms.label.ru' => 'Номеров',
                'about_stats.rooms.value.en' => '52',
                'about_stats.rooms.value.ru' => '52',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $stored = HotelSetting::query()->where('key', 'about_stats')->firstOrFail()->value;

        $this->assertSame('25+', $stored['years']['value']['en']);
        $this->assertSame('Лет', $stored['years']['label']['ru']);
        $this->assertSame('52', $stored['rooms']['value']['en']);
    }

    public function test_the_api_sends_both_languages_for_every_badge_with_defaults_filling_the_gaps(): void
    {
        // Only one badge, one field, customised; everything else should default.
        HotelSetting::factory()->create([
            'key' => 'about_stats',
            'value' => ['years' => ['value' => ['en' => '25+']]],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('hotel.about_stats.years.value.en', '25+')
                ->where('hotel.about_stats.years.value.ru', '20+')
                ->where('hotel.about_stats.years.label.ru', 'Лет')
                ->where('hotel.about_stats.rooms.label.en', 'Rooms')
                ->where('hotel.about_stats.guests.value.en', '30k'));
    }

    public function test_reopening_the_settings_page_shows_the_previously_saved_translations(): void
    {
        HotelSetting::query()->create([
            'key' => 'about_stats',
            'value' => ['guests' => ['label' => ['en' => 'Visitors', 'ru' => 'Посетителей']]],
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test(ManageHotelSettings::class)
            ->assertFormSet([
                'about_stats.guests.label.en' => 'Visitors',
                'about_stats.guests.label.ru' => 'Посетителей',
                // A badge the admin never touched still comes back filled with the default.
                'about_stats.years.label.en' => 'Years',
                'about_stats.years.value.en' => '20+',
            ]);
    }
}
