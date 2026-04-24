<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('mail_event_user_confirmed_subject')->nullable()->after('mail_admin_canceled_body');
            $table->text('mail_event_user_confirmed_body')->nullable()->after('mail_event_user_confirmed_subject');
            $table->string('mail_event_user_canceled_subject')->nullable()->after('mail_event_user_confirmed_body');
            $table->text('mail_event_user_canceled_body')->nullable()->after('mail_event_user_canceled_subject');
            $table->string('mail_event_admin_confirmed_subject')->nullable()->after('mail_event_user_canceled_body');
            $table->text('mail_event_admin_confirmed_body')->nullable()->after('mail_event_admin_confirmed_subject');
            $table->string('mail_event_admin_canceled_subject')->nullable()->after('mail_event_admin_confirmed_body');
            $table->text('mail_event_admin_canceled_body')->nullable()->after('mail_event_admin_canceled_subject');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'mail_event_user_confirmed_subject',
                'mail_event_user_confirmed_body',
                'mail_event_user_canceled_subject',
                'mail_event_user_canceled_body',
                'mail_event_admin_confirmed_subject',
                'mail_event_admin_confirmed_body',
                'mail_event_admin_canceled_subject',
                'mail_event_admin_canceled_body',
            ]);
        });
    }
};
