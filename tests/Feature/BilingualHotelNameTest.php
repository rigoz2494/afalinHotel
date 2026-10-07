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
 * The hotel's own name is stored per locale, the same way section headings
 * are, so "Афалина" shows for Russian guests everywhere the brand
 * appears — the header, the <title> tag and OpenGraph/Twitter share cards.
 */
class BilingualHotelNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_both_languages_from_the_locale_tabs(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageHotelSettings::class)
            ->fillForm([
                'hotel_name.en' => 'Afalina',
                'hotel_name.ru' => 'Афалина',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $stored = HotelSetting::query()->where('key', 'hotel_name')->firstOrFail()->value;

        $this->assertSame('Afalina', $stored['en']);
        $this->assertSame('Афалина', $stored['ru']);
    }

    public function test_a_fresh_install_defaults_to_the_official_brand_name_in_both_languages(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('hotel.hotel_name.en', 'Afalina')
                ->where('hotel.hotel_name.ru', 'Афалина'));
    }

    public function test_pre_translation_english_only_text_is_preserved_not_discarded(): void
    {
        // The shape every hotel_name row had before this feature existed.
        HotelSetting::query()->create(['key' => 'hotel_name', 'value' => 'The Old Lighthouse Inn']);

        $name = HotelSetting::defaultedHotelName(
            HotelSetting::query()->where('key', 'hotel_name')->first()->value,
        );

        $this->assertSame('The Old Lighthouse Inn', $name['en']);
        // Russian wasn't set before this feature existed, so it defaults.
        $this->assertSame('Афалина', $name['ru']);
    }

    public function test_reopening_the_settings_page_shows_the_previously_saved_translation(): void
    {
        HotelSetting::query()->create(['key' => 'hotel_name', 'value' => ['en' => 'Afalina', 'ru' => 'Афалина']]);

        $this->actingAs(User::factory()->create());

        Livewire::test(ManageHotelSettings::class)
            ->assertFormSet([
                'hotel_name.en' => 'Afalina',
                'hotel_name.ru' => 'Афалина',
            ]);
    }
}
