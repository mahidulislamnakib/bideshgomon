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
        Schema::table('user_notification_preferences', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (! Schema::hasColumn('user_notification_preferences', 'email_enabled')) {
                $table->boolean('email_enabled')->default(true)->after('notification_type');
            }
            if (! Schema::hasColumn('user_notification_preferences', 'in_app_enabled')) {
                $table->boolean('in_app_enabled')->default(true)->after('email_enabled');
            }
            if (! Schema::hasColumn('user_notification_preferences', 'push_enabled')) {
                $table->boolean('push_enabled')->default(true)->after('in_app_enabled');
            }
        });

        // Copy data from old columns to new columns if old columns exist
        if (Schema::hasColumn('user_notification_preferences', 'email')) {
            \DB::statement('UPDATE user_notification_preferences SET email_enabled = email WHERE email_enabled IS NULL OR email_enabled = 1');
        }
        if (Schema::hasColumn('user_notification_preferences', 'push')) {
            \DB::statement('UPDATE user_notification_preferences SET push_enabled = push WHERE push_enabled IS NULL OR push_enabled = 1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_notification_preferences', function (Blueprint $table) {
            $table->dropColumn(['email_enabled', 'in_app_enabled', 'push_enabled']);
        });
    }
};
