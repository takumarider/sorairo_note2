<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('welcome_card_padding')->nullable()->after('welcome_shop_paragraph_mode');
            $table->string('welcome_card_radius')->nullable()->after('welcome_card_padding');
            $table->string('welcome_card_shadow')->nullable()->after('welcome_card_radius');
            $table->string('welcome_font_style')->nullable()->after('welcome_card_shadow');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'welcome_card_padding',
                'welcome_card_radius',
                'welcome_card_shadow',
                'welcome_font_style',
            ]);
        });
    }
};
