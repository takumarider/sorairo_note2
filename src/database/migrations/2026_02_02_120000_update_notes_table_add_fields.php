<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('title')->default('')->comment('タイトル');
            $table->text('content')->nullable()->comment('本文');
            $table->string('image_path')->nullable()->comment('画像パス');
            $table->boolean('is_published')->default(true)->comment('公開フラグ');
            $table->timestamp('published_at')->nullable()->comment('公開日時');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['title', 'content', 'image_path', 'is_published', 'published_at']);
        });
    }
};
