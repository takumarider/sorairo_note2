<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // 予約時点の合計料金・合計所要時間を保持する（メニュー/オプションの後日変更から予約履歴を守るため）。
            // 既存データとの互換性のため nullable とし、未設定の場合はモデル側でメニュー・オプションから算出する。
            $table->integer('total_price')->nullable()->after('end_time')->comment('予約時点の合計料金（円）');
            $table->integer('total_duration')->nullable()->after('total_price')->comment('予約時点の合計所要時間（分）');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['total_price', 'total_duration']);
        });
    }
};
