<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // メニュー名
            $table->text('description')->nullable();       // 説明
            $table->integer('price');                      // 料金（円）
            $table->integer('duration');                   // 所要時間（分）
            $table->string('image_path')->nullable();      // 画像パス
            $table->boolean('is_active')->default(true);   // 有効フラグ
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
