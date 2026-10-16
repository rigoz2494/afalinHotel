<?php

namespace Tests\Feature;

use App\Jobs\TranslateBookingDetails;
use App\Models\CallbackRequest;
use App\Models\User;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * CallbackRequestObserver queues a TranslateBookingDetails job to fill in a
 * Russian translation of both a guest's message and their special_requests
 * — automatic, not a manual click (unlike the FAQ "Auto-Translate" action,
 * which uses the same TranslationService synchronously since an admin is
 * actively waiting on that one). Queued, not run inline, specifically so a
 * slow or unreachable translation API can never delay the guest's own
 * booking confirmation.
 */
class CallbackRequestTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_non_russian_special_request_is_automatically_translated_on_creation(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::response([
            'responseData' => ['translatedText' => 'Тихая сторона, пожалуйста.'],
        ])]);
        Queue::fake([TranslateBookingDetails::class]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'special_requests' => 'Quiet side, please.',
        ])->assertCreated();

        // Queued, not run inline — executed here exactly as a real queue
        // worker would, off the request/response cycle.
        Queue::assertPushed(function (TranslateBookingDetails $job): bool {
            $job->handle(app(TranslationService::class));

            return true;
        });

        $this->assertSame(
            'Тихая сторона, пожалуйста.',
            CallbackRequest::first()->special_requests_translated,
        );
    }

    public function test_a_non_russian_message_is_also_automatically_translated_on_creation(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::response([
            'responseData' => ['translatedText' => 'Благодарим, увидимся скоро.'],
        ])]);
        Queue::fake([TranslateBookingDetails::class]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'message' => 'Thank you, see you soon.',
        ])->assertCreated();

        Queue::assertPushed(function (TranslateBookingDetails $job): bool {
            $job->handle(app(TranslationService::class));

            return true;
        });

        $this->assertSame(
            'Благодарим, увидимся скоро.',
            CallbackRequest::first()->message_translated,
        );
    }

    public function test_the_message_and_special_request_are_translated_independently(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::sequence()
            ->push(['responseData' => ['translatedText' => 'Переведённое сообщение.']])
            ->push(['responseData' => ['translatedText' => 'Переведённый запрос.']])]);
        Queue::fake([TranslateBookingDetails::class]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'message' => 'Translated message.',
            'special_requests' => 'Translated request.',
        ])->assertCreated();

        Queue::assertPushed(function (TranslateBookingDetails $job): bool {
            $job->handle(app(TranslationService::class));

            return true;
        });

        $booking = CallbackRequest::first();

        $this->assertSame('Переведённое сообщение.', $booking->message_translated);
        $this->assertSame('Переведённый запрос.', $booking->special_requests_translated);
    }

    public function test_a_special_request_already_in_russian_is_not_translated(): void
    {
        Http::fake();
        Queue::fake([TranslateBookingDetails::class]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'special_requests' => 'Тихая сторона, пожалуйста.',
        ])->assertCreated();

        // The job is still queued unconditionally — it's TranslationService's
        // own Cyrillic check, inside the job, that decides there's nothing
        // to translate, so running it sends no HTTP request at all.
        Queue::assertPushed(function (TranslateBookingDetails $job): bool {
            $job->handle(app(TranslationService::class));

            return true;
        });

        $this->assertNull(CallbackRequest::first()->special_requests_translated);
        Http::assertNothingSent();
    }

    public function test_a_blank_special_request_is_not_sent_for_translation(): void
    {
        Http::fake();
        Queue::fake([TranslateBookingDetails::class]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
        ])->assertCreated();

        Queue::assertPushed(function (TranslateBookingDetails $job): bool {
            $job->handle(app(TranslationService::class));

            return true;
        });

        $this->assertNull(CallbackRequest::first()->special_requests_translated);
        Http::assertNothingSent();
    }

    public function test_the_booking_still_saves_when_the_translation_service_is_unreachable(): void
    {
        Http::fake(['api.mymemory.translated.net/*' => Http::response([], 500)]);
        Queue::fake([TranslateBookingDetails::class]);

        $this->postJson(route('api.v1.callback-requests.store'), [
            'name' => 'Jane',
            'phone' => '5550101234',
            'special_requests' => 'Quiet side, please.',
        ])->assertCreated();

        // Even run for real, a failed translation never throws out of the
        // job — it's caught the same way TranslationService always has been.
        Queue::assertPushed(function (TranslateBookingDetails $job): bool {
            $job->handle(app(TranslationService::class));

            return true;
        });

        $this->assertNull(CallbackRequest::first()->special_requests_translated);
    }

    public function test_the_admin_form_shows_both_translations_in_the_unified_box(): void
    {
        // Both fields are already Russian here specifically so the observer
        // sees nothing to translate and leaves these explicitly set
        // *_translated values alone — this test is about the admin form's
        // display, not the observer's own behavior (covered above).
        $callbackRequest = CallbackRequest::factory()->create([
            'message' => 'Спасибо, скоро увидимся (оригинал).',
            'message_translated' => 'Спасибо, скоро увидимся.',
            'special_requests' => 'Тихая сторона, пожалуйста (оригинал).',
            'special_requests_translated' => 'Тихая сторона, пожалуйста.',
        ]);

        $this->actingAs(User::factory()->create())
            ->get("/admin/callback-requests/{$callbackRequest->id}/edit")
            ->assertOk()
            ->assertSee('Спасибо, скоро увидимся.')
            ->assertSee('Тихая сторона, пожалуйста.');
    }

    public function test_the_admin_form_hides_the_translation_block_when_there_is_none(): void
    {
        $callbackRequest = CallbackRequest::factory()->create([
            'message' => null,
            'message_translated' => null,
            'special_requests' => null,
            'special_requests_translated' => null,
        ]);

        $this->actingAs(User::factory()->create())
            ->get("/admin/callback-requests/{$callbackRequest->id}/edit")
            ->assertOk()
            ->assertDontSee('Automatically');
    }
}
