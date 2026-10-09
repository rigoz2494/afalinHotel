<?php

namespace Tests\Feature;

use App\Enums\CallbackRequestStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_defaults_to_english(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/manage-hotel-settings')
            ->assertOk()
            ->assertSee('Site Settings');
    }

    public function test_admin_renders_in_russian_when_the_session_locale_is_russian(): void
    {
        $this->actingAs(User::factory()->create())
            ->withSession(['locale' => 'ru'])
            ->get('/admin/manage-hotel-settings')
            ->assertOk()
            ->assertSee('Настройки сайта')
            ->assertDontSee('Site Settings');
    }

    public function test_status_labels_follow_the_active_locale(): void
    {
        app()->setLocale('ru');

        $this->assertSame('Отменено', CallbackRequestStatus::Cancelled->getLabel());
        $this->assertSame('Ожидает', CallbackRequestStatus::New->getLabel());

        app()->setLocale('en');

        $this->assertSame('Cancelled', CallbackRequestStatus::Cancelled->getLabel());
    }

    /**
     * The actual EN/RU control a manager clicks — provided zero-config by
     * bezhansalleh/filament-language-switch (configured in
     * AppServiceProvider), which both renders this dropdown and sets
     * `session('locale')` when clicked. Without this, a manager has no way
     * to reach the Russian translations at all, even though they exist.
     */
    public function test_the_language_switcher_control_is_rendered_in_the_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/manage-hotel-settings')
            ->assertOk()
            ->assertSee('language-switch-trigger', escape: false);
    }
}
