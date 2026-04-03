<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete()->comment('予約ID');
            $table->foreignId('menu_option_id')->constrained()->cascadeOnDelete()->comment('メニューオプションID');
            $table->timestamps();

            $table->unique(['reservation_id', 'menu_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_options');
    }
};
