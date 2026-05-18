<?php

namespace Database\Seeders;

use App\Models\User;
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
        if (app()->environment('production')) {
            $this->command->warn('DatabaseSeeder は本番環境では実行されません。スキップします。');

            return;
        }

        $this->call(SystemSettingSeeder::class);

        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('ADMIN_NAME', 'izumi'),
                'password' => env('ADMIN_PASSWORD', 'sorairo_admin'),
                'is_admin' => true,
            ]
        );
    }
}
