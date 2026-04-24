<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->boolean('is_event')->default(false)->after('image_path')->comment('イベントメニューかどうか');
            $table->index('is_event');
        });

        Schema::table('slots', function (Blueprint $table) {
            $table->unsignedInteger('capacity')->nullable()->after('end_time')->comment('イベント定員');
        });
    }

    public function down(): void
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['is_event']);
            $table->dropColumn('is_event');
        });
    }
};
