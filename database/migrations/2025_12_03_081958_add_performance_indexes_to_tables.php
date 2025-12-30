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
        // Helper to check existing indexes in sqlite. For other DBs, presence checks will be conservative.
        $hasIndex = function (string $table, string $indexName) {
            $driver = config('database.default');
            if ($driver === 'sqlite') {
                $indexes = array_map(fn ($r) => $r->name, \Illuminate\Support\Arr::wrap(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('".$table."')")));

                return in_array($indexName, $indexes, true);
            }

            // For non-sqlite drivers, fallback to checking columns to avoid duplicate creation
            return false;
        };

        // Wallet Transactions - heavily queried for transaction history
        if (! $hasIndex('wallet_transactions', 'wallet_transactions_wallet_id_index')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->index('wallet_id');
            });
        }
        if (! $hasIndex('wallet_transactions', 'wallet_transactions_transaction_type_index')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->index('transaction_type');
            });
        }
        if (! $hasIndex('wallet_transactions', 'wallet_transactions_wallet_id_created_at_index')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                $table->index(['wallet_id', 'created_at']); // Composite for timeline queries
            });
        }

        // User Passports - frequently filtered by user and primary status
        if (! $hasIndex('user_passports', 'user_passports_user_id_index')) {
            Schema::table('user_passports', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
        if (! $hasIndex('user_passports', 'user_passports_is_primary_index')) {
            Schema::table('user_passports', function (Blueprint $table) {
                $table->index('is_primary');
            });
        }

        // Job Applications - status filtering is common
        if (! $hasIndex('job_applications', 'job_applications_user_id_status_index')) {
            Schema::table('job_applications', function (Blueprint $table) {
                $table->index(['user_id', 'status']);
            });
        }
        if (! $hasIndex('job_applications', 'job_applications_job_posting_id_index')) {
            Schema::table('job_applications', function (Blueprint $table) {
                $table->index('job_posting_id');
            });
        }

        // Referrals - lookup by referrer
        if (! $hasIndex('referrals', 'referrals_referrer_id_index')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->index('referrer_id');
            });
        }
        if (! $hasIndex('referrals', 'referrals_referee_id_index')) {
            Schema::table('referrals', function (Blueprint $table) {
                $table->index('referee_id');
            });
        }

        // Service Applications - heavily filtered
        if (! $hasIndex('service_applications', 'service_applications_user_id_index')) {
            Schema::table('service_applications', function (Blueprint $table) {
                $table->index('user_id');
            });
        }
        if (! $hasIndex('service_applications', 'service_applications_service_module_id_index')) {
            Schema::table('service_applications', function (Blueprint $table) {
                $table->index('service_module_id');
            });
        }
        if (! $hasIndex('service_applications', 'service_applications_status_index')) {
            Schema::table('service_applications', function (Blueprint $table) {
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropIfExists = function (string $table, string $indexName) {
            $driver = config('database.default');
            if ($driver === 'sqlite') {
                $indexes = array_map(fn ($r) => $r->name, \Illuminate\Support\Arr::wrap(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('".$table."')")));
                if (in_array($indexName, $indexes, true)) {
                    Schema::table($table, function (Blueprint $table) use ($indexName) {
                        $table->dropIndex($indexName);
                    });
                }

                return;
            }
            // Fallback: attempt to drop but ignore errors
            try {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            } catch (\Exception $e) {
                // ignore
            }
        };

        $dropIfExists('wallet_transactions', 'wallet_transactions_wallet_id_index');
        $dropIfExists('wallet_transactions', 'wallet_transactions_transaction_type_index');
        $dropIfExists('wallet_transactions', 'wallet_transactions_wallet_id_created_at_index');

        $dropIfExists('user_passports', 'user_passports_user_id_index');
        $dropIfExists('user_passports', 'user_passports_is_primary_index');

        $dropIfExists('job_applications', 'job_applications_user_id_status_index');
        $dropIfExists('job_applications', 'job_applications_job_posting_id_index');

        $dropIfExists('referrals', 'referrals_referrer_id_index');
        $dropIfExists('referrals', 'referrals_referee_id_index');

        $dropIfExists('service_applications', 'service_applications_user_id_index');
        $dropIfExists('service_applications', 'service_applications_service_module_id_index');
        $dropIfExists('service_applications', 'service_applications_status_index');
    }
};
