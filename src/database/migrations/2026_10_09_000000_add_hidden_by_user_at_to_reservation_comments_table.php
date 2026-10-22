<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_comments', function (Blueprint $table) {
            $table->timestamp('hidden_by_user_at')->nullable()->after('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_comments', function (Blueprint $table) {
            $table->dropColumn('hidden_by_user_at');
        });
    }
};
