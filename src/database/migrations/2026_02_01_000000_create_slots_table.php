<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete()->comment('メニューID');
            $table->date('date')->comment('日付');
            $table->time('start_time')->comment('開始時間');
            $table->time('end_time')->comment('終了時間');
            $table->boolean('is_reserved')->default(false)->comment('予約済フラグ');
            $table->timestamps();

            $table->unique(['menu_id', 'date', 'start_time'], 'unique_slot');
            $table->index(['date', 'is_reserved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
