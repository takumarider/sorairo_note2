<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_comments', function (Blueprint $table) {
            $table->timestamp('deleted_by_admin_at')->nullable()->after('hidden_by_user_at');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_comments', function (Blueprint $table) {
            $table->dropColumn('deleted_by_admin_at');
        });
    }
};
