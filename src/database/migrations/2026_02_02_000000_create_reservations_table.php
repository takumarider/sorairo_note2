<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->comment('ユーザーID');
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete()->comment('メニューID');
            $table->foreignId('slot_id')->constrained()->cascadeOnDelete()->comment('時間枠ID');
            $table->enum('status', ['confirmed', 'canceled', 'completed'])
                ->default('confirmed')
                ->comment('予約ステータス');
            $table->timestamp('canceled_at')->nullable()->comment('キャンセル日時');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('slot_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
