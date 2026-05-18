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
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('welcome_theme_background')->nullable()->after('welcome_instagram_url');
            $table->string('welcome_theme_accent')->nullable()->after('welcome_theme_background');

            $table->string('welcome_hero_title_size')->nullable()->after('welcome_theme_accent');
            $table->string('welcome_hero_title_color')->nullable()->after('welcome_hero_title_size');
            $table->string('welcome_hero_subtitle_size')->nullable()->after('welcome_hero_title_color');
            $table->string('welcome_hero_subtitle_color')->nullable()->after('welcome_hero_subtitle_size');
            $table->string('welcome_hero_lead_size')->nullable()->after('welcome_hero_subtitle_color');
            $table->string('welcome_hero_lead_color')->nullable()->after('welcome_hero_lead_size');
            $table->string('welcome_hero_text_align')->nullable()->after('welcome_hero_lead_color');
            $table->string('welcome_hero_lead_paragraph_mode')->nullable()->after('welcome_hero_text_align');

            $table->string('welcome_shop_title_size')->nullable()->after('welcome_hero_lead_paragraph_mode');
            $table->string('welcome_shop_title_color')->nullable()->after('welcome_shop_title_size');
            $table->string('welcome_shop_body_size')->nullable()->after('welcome_shop_title_color');
            $table->string('welcome_shop_body_color')->nullable()->after('welcome_shop_body_size');
            $table->string('welcome_shop_paragraph_mode')->nullable()->after('welcome_shop_body_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'welcome_theme_background',
                'welcome_theme_accent',
                'welcome_hero_title_size',
                'welcome_hero_title_color',
                'welcome_hero_subtitle_size',
                'welcome_hero_subtitle_color',
                'welcome_hero_lead_size',
                'welcome_hero_lead_color',
                'welcome_hero_text_align',
                'welcome_hero_lead_paragraph_mode',
                'welcome_shop_title_size',
                'welcome_shop_title_color',
                'welcome_shop_body_size',
                'welcome_shop_body_color',
                'welcome_shop_paragraph_mode',
            ]);
        });
    }
};
