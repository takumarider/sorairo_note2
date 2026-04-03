<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week')->nullable()->comment('曜日 (0=日, 1=月, ..., 6=土)');
            $table->date('specific_date')->nullable()->comment('特定日指定（曜日を上書き）');
            $table->time('open_time')->comment('営業開始時間');
            $table->time('close_time')->comment('営業終了時間');
            $table->boolean('is_closed')->default(false)->comment('休業フラグ');
            $table->timestamps();

            $table->index('day_of_week');
            $table->index('specific_date');
            $table->unique(['day_of_week', 'specific_date'], 'unique_business_hour');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};
