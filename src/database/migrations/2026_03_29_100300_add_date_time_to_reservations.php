<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // slot_id を nullable に変更
            $table->foreignId('slot_id')->nullable()->change();

            // date, start_time, end_time を追加
            $table->date('date')->nullable()->after('slot_id')->comment('予約日');
            $table->time('start_time')->nullable()->after('date')->comment('開始時刻');
            $table->time('end_time')->nullable()->after('start_time')->comment('終了時刻');

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // 新しく追加したカラムを削除
            $table->dropIndex(['date']);
            $table->dropColumn(['date', 'start_time', 'end_time']);

            // slot_id を元に戻す
            $table->foreignId('slot_id')->change();
        });
    }
};
