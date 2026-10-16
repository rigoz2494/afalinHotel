<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageHotelSettings;
use App\Models\HotelSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Site Settings "Admin Account Settings" block is a thin, virtual
 * gateway onto the currently logged-in admin's own `users` row — never a
 * HotelSetting row of its own, since a plaintext password must never be
 * written into the generic settings JSON blob.
 */
class AdminCredentialsSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_their_own_login_email(): void
    {
        $user = User::factory()->create(['email' => 'admin@example.com']);

        $this->actingAs($user);

        Livewire::test(ManageHotelSettings::class)
            ->fillForm(['admin_credentials.email' => 'owner@afalina.example'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('owner@afalina.example', $user->fresh()->email);
    }

    public function test_admin_can_change_their_own_password_and_the_new_one_actually_works(): void
    {
        $user = User::factory()->create(['password' => 'old-password-123']);

        $this->actingAs($user);

        Livewire::test(ManageHotelSettings::class)
            ->fillForm(['admin_credentials.password' => 'new-password-456'])
            ->call('save')
            ->assertHasNoFormErrors();

        // Not double-hashed: the new plaintext password must verify
        // against the stored hash, via the model's own 'hashed' cast.
        $this->assertTrue(Hash::check('new-password-456', $user->fresh()->password));
        $this->assertFalse(Hash::check('old-password-123', $user->fresh()->password));
    }

    public function test_leaving_both_credential_fields_blank_changes_nothing(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'untouched-password',
        ]);
        $originalHash = $user->password;

        $this->actingAs($user);

        Livewire::test(ManageHotelSettings::class)
            ->fillForm(['admin_credentials.email' => '', 'admin_credentials.password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = $user->fresh();
        $this->assertSame('admin@example.com', $fresh->email);
        $this->assertSame($originalHash, $fresh->password);
    }

    public function test_changing_to_an_email_already_used_by_another_admin_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'admin@example.com']);

        $this->actingAs($user);

        Livewire::test(ManageHotelSettings::class)
            ->fillForm(['admin_credentials.email' => 'taken@example.com'])
            ->call('save')
            ->assertHasNoFormErrors();

        // Rejected silently (with its own notification, not a form error)
        // rather than crashing on the database's unique constraint.
        $this->assertSame('admin@example.com', $user->fresh()->email);
    }

    public function test_the_password_is_never_written_to_the_generic_settings_table(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ManageHotelSettings::class)
            ->fillForm(['admin_credentials.password' => 'should-never-be-stored-here'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull(HotelSetting::query()->where('key', 'admin_credentials')->first());
    }

    public function test_the_password_field_is_blank_again_after_saving(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ManageHotelSettings::class)
            ->fillForm(['admin_credentials.password' => 'temporary-password'])
            ->call('save')
            ->assertFormSet(['admin_credentials.password' => null]);
    }
}
