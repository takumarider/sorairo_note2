<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_publication_months', function (Blueprint $table) {
            $table->id();
            $table->string('year_month', 7)->unique()->comment('公開設定対象月 (YYYY-MM)');
            $table->boolean('is_published')->default(false)->comment('ユーザー向け公開フラグ');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_publication_months');
    }
};
