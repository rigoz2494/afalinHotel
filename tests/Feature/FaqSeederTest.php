<?php

namespace Tests\Feature;

use App\Models\Faq;
use Database\Seeders\FaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FaqSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_seeded_faq_is_translated_into_russian(): void
    {
        Http::fake();

        $this->seed(FaqSeeder::class);

        $faqs = Faq::query()->get();

        $this->assertCount(5, $faqs);
        $this->assertTrue($faqs->every(fn (Faq $faq): bool => filled($faq->question_ru) && filled($faq->answer_ru)));
        $this->assertSame('Во сколько заезд и выезд?', $faqs->first()->question_ru);
        Http::assertNothingSent();
    }

    public function test_seeding_again_does_not_duplicate_faqs(): void
    {
        $this->seed(FaqSeeder::class);
        $this->seed(FaqSeeder::class);

        $this->assertSame(5, Faq::query()->count());
    }
}
