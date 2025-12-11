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
        Schema::table('service_modules', function (Blueprint $table) {
            // Service delivery characteristics
            $table->boolean('requires_form_submission')->default(true)->after('service_type');
            $table->boolean('has_delivery_charges')->default(false)->after('requires_form_submission');
            $table->decimal('default_delivery_charge', 10, 2)->default(0.00)->after('has_delivery_charges');
            $table->boolean('is_query_based')->default(true)->after('default_delivery_charge');
            $table->boolean('is_marketplace')->default(false)->after('is_query_based');

            // University exclusivity support
            $table->boolean('supports_university_exclusivity')->default(false)->after('is_marketplace');

            // Agency type restrictions
            $table->foreignId('restricted_to_agency_type_id')->nullable()->after('supports_university_exclusivity')
                ->constrained('agency_types')->onDelete('set null');

            // Add indexes for performance
            $table->index('is_marketplace');
            $table->index('restricted_to_agency_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_modules', function (Blueprint $table) {
            $table->dropForeign(['restricted_to_agency_type_id']);
            $table->dropIndex(['is_marketplace']);
            $table->dropIndex(['restricted_to_agency_type_id']);

            $table->dropColumn([
                'requires_form_submission',
                'has_delivery_charges',
                'default_delivery_charge',
                'is_query_based',
                'is_marketplace',
                'supports_university_exclusivity',
                'restricted_to_agency_type_id',
            ]);
        });
    }
};
