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
}
