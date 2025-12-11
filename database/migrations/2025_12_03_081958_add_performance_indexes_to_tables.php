<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add indexes for performance optimization
     */
    public function up(): void
    {
        // Wallet Transactions - heavily queried for transaction history
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->index('wallet_id');
            $table->index('transaction_type');
            $table->index(['wallet_id', 'created_at']); // Composite for timeline queries
        });

        // User Passports - frequently filtered by user and primary status
        Schema::table('user_passports', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('is_primary');
        });

        // Job Applications - status filtering is common
        Schema::table('job_applications', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
            $table->index('job_posting_id');
        });

        // Referrals - lookup by referrer
        Schema::table('referrals', function (Blueprint $table) {
            $table->index('referrer_id');
            $table->index('referee_id');
        });

        // Service Applications - heavily filtered
        Schema::table('service_applications', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('service_module_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex(['wallet_id']);
            $table->dropIndex(['transaction_type']);
            $table->dropIndex(['wallet_id', 'created_at']);
        });

        Schema::table('user_passports', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_primary']);
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['job_posting_id']);
        });

        Schema::table('referrals', function (Blueprint $table) {
            $table->dropIndex(['referrer_id']);
            $table->dropIndex(['referee_id']);
        });

        Schema::table('service_applications', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['service_module_id']);
            $table->dropIndex(['status']);
        });
    }
};
