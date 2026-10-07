<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class NotFoundFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unknown_url_renders_the_branded_404_page(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page->component('NotFound'));
    }

    public function test_an_unknown_api_path_gets_a_json_404_instead_of_html(): void
    {
        $this->getJson('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Not found.']);
    }

    public function test_the_home_route_is_unaffected(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Landing'));
    }
}
