<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'andrea@artsmile.co'],
            [
                'name' => 'Andrea Vargas',
                'password' => Hash::make('artsmile-2026'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        $this->call(CourseSeeder::class);
    }
}
