<?php

namespace Tests\Feature;

use App\Models\Faq;
use Database\Seeders\FaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The seeder auto-translates its English mock FAQs into Russian the same
 * way the admin's "Auto-Translate" action does, so a fresh install never
 * ships FAQ content in English only.
 */
class FaqSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_faq_is_translated_into_russian(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::response([
            'responseData' => ['translatedText' => 'Переведённый текст'],
        ])]);

        $this->seed(FaqSeeder::class);

        $faqs = Faq::query()->get();

        $this->assertCount(5, $faqs);
        $this->assertTrue($faqs->every(fn (Faq $faq): bool => filled($faq->question_ru) && filled($faq->answer_ru)));
        $this->assertSame('Переведённый текст', $faqs->first()->question_ru);
    }

    public function test_seeding_again_does_not_duplicate_faqs(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::response([
            'responseData' => ['translatedText' => 'Переведённый текст'],
        ])]);

        $this->seed(FaqSeeder::class);
        $this->seed(FaqSeeder::class);

        $this->assertSame(5, Faq::query()->count());
    }
}
