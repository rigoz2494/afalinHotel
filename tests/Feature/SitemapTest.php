<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_sitemap_lists_the_homepage_and_every_active_room(): void
    {
        $active = Room::factory()->create(['slug' => 'ocean-view-suite']);
        $hidden = Room::factory()->inactive()->create(['slug' => 'back-office-room']);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSeeTextInOrder([route('home')]);
        $response->assertSee(route('rooms.show', $active), false);
        $response->assertDontSee(route('rooms.show', $hidden), false);
    }

    public function test_the_sitemap_is_valid_xml(): void
    {
        Room::factory()->count(2)->create();

        $response = $this->get(route('sitemap'));

        $document = new \DOMDocument;
        $this->assertTrue($document->loadXML($response->getContent()));
        $this->assertSame('urlset', $document->documentElement->tagName);
    }
}
