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
        // Rename only if the old column exists and new column doesn't already exist
        if (Schema::hasColumn('support_ticket_replies', 'is_admin_reply') && ! Schema::hasColumn('support_ticket_replies', 'is_staff_reply')) {
            Schema::table('support_ticket_replies', function (Blueprint $table) {
                $table->renameColumn('is_admin_reply', 'is_staff_reply');
            });
        }

        // Add new fields if they do not exist yet
        if (! Schema::hasColumn('support_ticket_replies', 'is_internal_note')) {
            Schema::table('support_ticket_replies', function (Blueprint $table) {
                $table->boolean('is_internal_note')->default(false);
            });
        }

        if (! Schema::hasColumn('support_ticket_replies', 'attachments')) {
            Schema::table('support_ticket_replies', function (Blueprint $table) {
                $table->json('attachments')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_ticket_replies', function (Blueprint $table) {
            $table->renameColumn('is_staff_reply', 'is_admin_reply');
            $table->dropColumn(['is_internal_note', 'attachments']);
        });
    }
};
