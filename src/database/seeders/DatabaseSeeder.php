<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SystemSettingSeeder::class);

        if (User::where('email', env('ADMIN_EMAIL'))->exists()) {
            User::create([
                'name' => env('ADMIN_NAME','izumi'),
                'email' => env('ADMIN_EMAIL','admin@example.com'),
                'password' => env('ADMIN_PASSWORD','sorairo_admin'),
                'is_admin' => true,
            ]);
        }

        $this->call(SystemSettingSeeder::class);
    }
}
