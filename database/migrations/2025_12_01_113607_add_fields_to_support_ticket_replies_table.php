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
        Schema::table('support_ticket_replies', function (Blueprint $table) {
            // Rename is_admin_reply to is_staff_reply for consistency
            $table->renameColumn('is_admin_reply', 'is_staff_reply');
            
            // Add new fields
            $table->boolean('is_internal_note')->default(false)->after('is_admin_reply');
            $table->json('attachments')->nullable()->after('message');
        });
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
