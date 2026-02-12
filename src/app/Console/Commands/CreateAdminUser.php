<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '環境変数から管理者アカウントを作成します';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 環境変数から取得
        $name = env('ADMIN_NAME');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        // バリデーション
        if (!$name || !$email || !$password) {
            $this->error('❌ 環境変数が設定されていません');
            $this->error('必要な環境変数:');
            $this->error('  - ADMIN_NAME');
            $this->error('  - ADMIN_EMAIL');
            $this->error('  - ADMIN_PASSWORD');
            return Command::FAILURE;
        }

        try {
            // 管理者アカウントを作成または更新
            $admin = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                    'is_admin' => true,
                ]
            );

            $this->info('========================================');
            $this->info('✅ 管理者アカウントを作成しました');
            $this->info('========================================');
            $this->info('ID: ' . $admin->id);
            $this->info('Name: ' . $admin->name);
            $this->info('Email: ' . $admin->email);
            $this->info('Is Admin: ' . ($admin->is_admin ? 'Yes' : 'No'));
            $this->info('Created: ' . $admin->created_at->format('Y-m-d H:i:s'));
            $this->info('========================================');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ エラーが発生しました: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}