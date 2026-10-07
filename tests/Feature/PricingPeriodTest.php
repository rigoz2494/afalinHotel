<?php

namespace Tests\Feature;

use App\Models\PricingPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_excludes_inactive_periods(): void
    {
        $active = PricingPeriod::factory()->create();
        PricingPeriod::factory()->inactive()->create();

        $result = PricingPeriod::query()->active()->get();

        $this->assertCount(1, $result);
        $this->assertSame($active->id, $result->first()->id);
    }

    public function test_ordered_scope_sorts_by_sort_order_then_id(): void
    {
        $third = PricingPeriod::factory()->create(['sort_order' => 3]);
        $first = PricingPeriod::factory()->create(['sort_order' => 1]);
        $second = PricingPeriod::factory()->create(['sort_order' => 2]);

        $result = PricingPeriod::query()->ordered()->get();

        $this->assertSame([$first->id, $second->id, $third->id], $result->pluck('id')->all());
    }
}
