<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Without this seeder, a freshly migrated database has no way to log into
 * /admin at all — there's no public registration (see User::canAccessPanel).
 */
class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_default_admin_can_log_in_with_the_documented_credentials(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertTrue(Auth::attempt(['email' => 'admin@example.com', 'password' => 'secret']));
    }

    public function test_seeding_again_does_not_duplicate_or_change_the_admin_account(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertSame(1, User::query()->where('email', 'admin@example.com')->count());
        $this->assertTrue(Auth::attempt(['email' => 'admin@example.com', 'password' => 'secret']));
    }
}
