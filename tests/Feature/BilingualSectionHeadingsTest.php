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
 * Section headings (the title at the top of each landing-page section, and
 * in the nav) are now stored per locale, so switching the guest's language
 * on the live site shows the admin's own translated title, not just English.
 */
class BilingualSectionHeadingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_both_languages_for_every_section_from_the_locale_tabs(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageHotelSettings::class)
            ->fillForm([
                'hotel_name' => 'Grand Meridian',
                'section_headings.rooms.en' => 'Our Rooms',
                'section_headings.rooms.ru' => 'Наши номера',
                'section_headings.contact.en' => 'Get in Touch',
                'section_headings.contact.ru' => 'Связаться с нами',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $stored = HotelSetting::query()->where('key', 'section_headings')->firstOrFail()->value;

        $this->assertSame('Our Rooms', $stored['rooms']['en']);
        $this->assertSame('Наши номера', $stored['rooms']['ru']);
        $this->assertSame('Get in Touch', $stored['contact']['en']);
        $this->assertSame('Связаться с нами', $stored['contact']['ru']);
    }

    public function test_the_api_sends_both_languages_for_every_section_with_defaults_filling_the_gaps(): void
    {
        HotelSetting::factory()->create(['key' => 'hotel_name', 'value' => 'Grand Meridian']);
        // Only one section, one locale, customised; everything else should default.
        HotelSetting::factory()->create([
            'key' => 'section_headings',
            'value' => ['rooms' => ['en' => 'Our Suites']],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('hotel.section_headings.rooms.en', 'Our Suites')
                ->where('hotel.section_headings.rooms.ru', 'Наши номера')
                ->where('hotel.section_headings.contact.en', 'Request a Callback')
                ->where('hotel.section_headings.contact.ru', 'Заказать обратный звонок'));
    }

    public function test_pre_translation_english_only_text_is_preserved_not_discarded(): void
    {
        // The shape every section_headings row had before this feature existed.
        HotelSetting::query()->create([
            'key' => 'section_headings',
            'value' => ['rooms' => 'Deluxe Rooms & Suites'],
        ]);

        $headings = HotelSetting::defaultedSectionHeadings(
            HotelSetting::query()->where('key', 'section_headings')->first()->value,
        );

        $this->assertSame('Deluxe Rooms & Suites', $headings['rooms']['en']);
        // Russian wasn't set before this feature existed, so it defaults.
        $this->assertSame('Наши номера', $headings['rooms']['ru']);
    }

    public function test_reopening_the_settings_page_shows_the_previously_saved_translations(): void
    {
        HotelSetting::query()->create([
            'key' => 'section_headings',
            'value' => ['faq' => ['en' => 'Questions', 'ru' => 'Вопросы']],
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test(ManageHotelSettings::class)
            ->assertFormSet([
                'section_headings.faq.en' => 'Questions',
                'section_headings.faq.ru' => 'Вопросы',
                // A section the admin never touched still comes back filled with the default.
                'section_headings.about.en' => 'A story of hospitality by the water',
            ]);
    }
}
