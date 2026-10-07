<?php

namespace Tests\Feature;

use App\Exceptions\TranslationException;
use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Resources\Faqs\Pages\EditFaq;
use App\Models\Faq;
use App\Models\User;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The "Auto-Translate" action is bidirectional: it detects which language was
 * actually typed, rather than assuming the primary fields are always English,
 * and fills in whichever pair is empty.
 */
class FaqTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_text_in_the_primary_fields_is_translated_into_the_russian_fields(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::sequence()
            ->push(['responseData' => ['translatedText' => 'Когда заезд?']])
            ->push(['responseData' => ['translatedText' => 'С 15:00.']])]);

        $result = app(TranslationService::class)->translateFaqFields([
            'question' => 'When is check-in?',
            'answer' => 'From 3pm.',
            'question_ru' => null,
            'answer_ru' => null,
        ]);

        $this->assertSame([
            'question_ru' => 'Когда заезд?',
            'answer_ru' => 'С 15:00.',
        ], $result);
    }

    public function test_russian_text_in_the_russian_fields_is_translated_into_the_primary_fields(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::sequence()
            ->push(['responseData' => ['translatedText' => 'When is check-in?']])
            ->push(['responseData' => ['translatedText' => 'From 3pm.']])]);

        $result = app(TranslationService::class)->translateFaqFields([
            'question' => '',
            'answer' => '',
            'question_ru' => 'Когда заезд?',
            'answer_ru' => 'С 15:00.',
        ]);

        $this->assertSame([
            'question' => 'When is check-in?',
            'answer' => 'From 3pm.',
        ], $result);
    }

    public function test_russian_text_typed_into_the_primary_fields_is_still_detected_and_moved_correctly(): void
    {
        // The admin typed Russian into the "primary" fields instead of the
        // "_ru" ones. The action must still end up with English in the
        // primary fields and Russian in the "_ru" fields, not the reverse.
        Http::fake(['api.mymemory.translated.net/*' => Http::sequence()
            ->push(['responseData' => ['translatedText' => 'When is check-in?']])
            ->push(['responseData' => ['translatedText' => 'From 3pm.']])]);

        $result = app(TranslationService::class)->translateFaqFields([
            'question' => 'Когда заезд?',
            'answer' => 'С 15:00.',
            'question_ru' => null,
            'answer_ru' => null,
        ]);

        $this->assertSame([
            'question_ru' => 'Когда заезд?',
            'answer_ru' => 'С 15:00.',
            'question' => 'When is check-in?',
            'answer' => 'From 3pm.',
        ], $result);
    }

    public function test_translating_when_both_language_pairs_already_have_text_fails_clearly(): void
    {
        $this->expectException(TranslationException::class);

        app(TranslationService::class)->translateFaqFields([
            'question' => 'When is check-in?',
            'answer' => 'From 3pm.',
            'question_ru' => 'Когда заезд?',
            'answer_ru' => 'С 15:00.',
        ]);
    }

    public function test_translating_when_nothing_has_been_typed_fails_clearly(): void
    {
        $this->expectException(TranslationException::class);

        app(TranslationService::class)->translateFaqFields([
            'question' => '',
            'answer' => '',
            'question_ru' => '',
            'answer_ru' => '',
        ]);
    }

    public function test_translate_fails_clearly_when_the_api_is_unreachable(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::response([], 500)]);

        $this->expectException(TranslationException::class);

        app(TranslationService::class)->translate('When is check-in?', 'en', 'ru');
    }

    public function test_admin_can_auto_translate_an_existing_faq_from_its_header_action(): void
    {
        $faq = Faq::factory()->create([
            'question' => 'When is check-in?',
            'answer' => 'From 3pm.',
            'question_ru' => null,
            'answer_ru' => null,
        ]);

        Http::fake(['api.mymemory.translated.net/*' => Http::sequence()
            ->push(['responseData' => ['translatedText' => 'Когда заезд?']])
            ->push(['responseData' => ['translatedText' => 'С 15:00.']])]);

        $this->actingAs(User::factory()->create());

        Livewire::test(EditFaq::class, ['record' => $faq->getRouteKey()])
            ->callAction('translateFaq')
            ->assertNotified('Translated');
    }

    public function test_admin_can_auto_translate_a_new_faq_before_saving_it_either_direction(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::sequence()
            ->push(['responseData' => ['translatedText' => 'Are pets allowed?']])
            ->push(['responseData' => ['translatedText' => 'Yes, for a small fee.']])]);

        $this->actingAs(User::factory()->create());

        $component = Livewire::test(CreateFaq::class)
            ->fillForm(['question_ru' => 'Разрешены ли животные?', 'answer_ru' => 'Да, за небольшую плату.'])
            ->callAction('translateFaq')
            ->assertNotified('Translated');

        $this->assertSame('Are pets allowed?', $component->get('data.question'));
        $this->assertSame('Yes, for a small fee.', $component->get('data.answer'));
    }

    public function test_admin_sees_a_failure_notification_when_the_translation_service_is_unreachable(): void
    {
        $faq = Faq::factory()->create();

        Http::fake(['api.mymemory.translated.net/*' => Http::response([], 500)]);

        $this->actingAs(User::factory()->create());

        Livewire::test(EditFaq::class, ['record' => $faq->getRouteKey()])
            ->callAction('translateFaq')
            ->assertNotified('Translation failed');
    }

    public function test_admin_sees_a_clear_notification_when_both_languages_already_have_content(): void
    {
        $faq = Faq::factory()->create([
            'question' => 'When is check-in?',
            'answer' => 'From 3pm.',
            'question_ru' => 'Когда заезд?',
            'answer_ru' => 'С 15:00.',
        ]);

        $this->actingAs(User::factory()->create());

        Livewire::test(EditFaq::class, ['record' => $faq->getRouteKey()])
            ->callAction('translateFaq')
            ->assertNotified('Translation failed');
    }

    public function test_translate_auto_detects_english_and_fills_in_the_russian_side(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::response([
            'responseData' => ['translatedText' => 'Шкаф'],
        ])]);

        $result = app(TranslationService::class)->translateAuto('Wardrobe');

        $this->assertSame(['en' => 'Wardrobe', 'ru' => 'Шкаф'], $result);
    }

    public function test_translate_auto_detects_russian_and_fills_in_the_english_side(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::response([
            'responseData' => ['translatedText' => 'Wardrobe'],
        ])]);

        $result = app(TranslationService::class)->translateAuto('Шкаф');

        $this->assertSame(['en' => 'Wardrobe', 'ru' => 'Шкаф'], $result);
    }

    public function test_translate_auto_returns_both_blank_for_blank_input(): void
    {
        $result = app(TranslationService::class)->translateAuto('   ');

        $this->assertSame(['en' => '', 'ru' => ''], $result);
    }
}
