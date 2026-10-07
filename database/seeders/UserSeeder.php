<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * The default admin account for a fresh install. There's no public
     * registration (see User::canAccessPanel()), so without this seeder a
     * freshly migrated database would have no way to log into /admin at
     * all. `admin` alone isn't a valid login here: Filament's own login
     * form requires a real email address, so this is the plain-ASCII
     * fallback the brief itself allows for.
     *
     * Change this password after the first login in any real deployment —
     * it's a known, public default, not a secret.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'secret', 'email_verified_at' => now()],
        );
    }
}
