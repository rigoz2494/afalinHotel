<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * public/robots.txt is a static file served directly by the web server, not
 * routed through Laravel, so this reads its contents rather than requesting
 * it over HTTP — there is no Laravel route for a test client to hit.
 */
class RobotsTxtTest extends TestCase
{
    public function test_it_allows_search_engines_to_index_the_public_site_but_blocks_the_backend(): void
    {
        $contents = file_get_contents(public_path('robots.txt'));

        $this->assertNotFalse($contents);
        $this->assertStringContainsString('Allow: /', $contents);
        $this->assertStringContainsString('Disallow: /admin', $contents);
        $this->assertStringContainsString('Disallow: /api', $contents);
        $this->assertStringContainsString('Googlebot', $contents);
        $this->assertStringContainsString('YandexBot', $contents);
        $this->assertStringContainsString('Sitemap:', $contents);
    }
}
