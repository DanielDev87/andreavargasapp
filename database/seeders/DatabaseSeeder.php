<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // The admin's credentials never live in the repository: they come from ADMIN_EMAIL /
        // ADMIN_PASSWORD, or a random password is generated and shown once in the console.
        $adminEmail = env('ADMIN_EMAIL') ?: 'admin@example.com';

        if (! User::where('email', $adminEmail)->exists()) {
            $password = env('ADMIN_PASSWORD') ?: Str::password(16);

            User::create([
                'name' => 'Andrea Vargas',
                'email' => $adminEmail,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]);

            if (! env('ADMIN_PASSWORD')) {
                $this->command?->warn("Admin {$adminEmail} creado con la contraseña temporal: {$password}");
            }
        }

        $this->call(CourseSeeder::class);
    }
}
