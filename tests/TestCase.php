<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    /**
     * No test should ever make a real network call (e.g. CallbackRequestObserver's
     * translation lookup, triggered by any English/German special_requests or
     * message) — TranslationService already treats a failed HTTP call as "no
     * translation available" rather than an error, so this guard just makes
     * every such call fail fast and offline instead of silently reaching the
     * real internet. A test that wants to exercise the real behavior fakes
     * it explicitly with `Http::fake([...])`.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        // The Inertia SSR renderer calls itself over local HTTP during a
        // request — a real, local, unrelated call this guard isn't for.
        Http::allowStrayRequests(['http://127.0.0.1:*', 'http://localhost:*']);
    }
}
