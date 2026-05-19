<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('welcome_page_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id')->default(1);
            $table->string('type');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'sort_order']);
            $table->index(['page_id', 'is_visible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('welcome_page_sections');
    }
};
