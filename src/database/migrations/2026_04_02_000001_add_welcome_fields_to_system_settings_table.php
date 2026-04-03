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
            $table->string('welcome_badge')->nullable()->after('notification_from_name');
            $table->string('welcome_title')->nullable()->after('welcome_badge');
            $table->string('welcome_subtitle')->nullable()->after('welcome_title');
            $table->text('welcome_lead')->nullable()->after('welcome_subtitle');
            $table->string('welcome_main_image_path')->nullable()->after('welcome_lead');
            $table->json('welcome_body_blocks')->nullable()->after('welcome_main_image_path');
            $table->string('welcome_shop_title')->nullable()->after('welcome_body_blocks');
            $table->text('welcome_shop_description')->nullable()->after('welcome_shop_title');
            $table->string('welcome_business_hours')->nullable()->after('welcome_shop_description');
            $table->string('welcome_regular_holiday')->nullable()->after('welcome_business_hours');
            $table->text('welcome_business_note')->nullable()->after('welcome_regular_holiday');
            $table->string('welcome_instagram_url')->nullable()->after('welcome_business_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'welcome_badge',
                'welcome_title',
                'welcome_subtitle',
                'welcome_lead',
                'welcome_main_image_path',
                'welcome_body_blocks',
                'welcome_shop_title',
                'welcome_shop_description',
                'welcome_business_hours',
                'welcome_regular_holiday',
                'welcome_business_note',
                'welcome_instagram_url',
            ]);
        });
    }
};
