<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (Schema::hasColumn('system_settings', 'is_next_month_public')) {
                $table->dropColumn('is_next_month_public');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('system_settings', 'is_next_month_public')) {
                $table->boolean('is_next_month_public')
                    ->default(false)
                    ->after('user_cancel_deadline_hours')
                    ->comment('ユーザー向け翌月予約枠公開フラグ');
            }
        });
    }
};
