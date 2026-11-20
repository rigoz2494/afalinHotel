<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicLandingContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_production_site_does_not_offer_demo_content(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Landing')
            ->where('showDemoData', false)
            ->has('rooms.data', 0)
            ->has('pricing.rows.data', 0)
            ->has('faqs.data', 0)
            ->has('gallery.data', 0)
            ->has('hotel.contacts', 0)
            ->where('hotel.promo_banner', null)
            ->where('hotel.about_story.ru', '')
            ->where('hotel.about_stats.rooms.value.ru', ''));
    }
}
