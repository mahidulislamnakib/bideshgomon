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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_module_id')->nullable()->constrained('service_modules')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable(); // For discount display
            $table->string('currency', 3)->default('BDT');
            $table->string('price_unit')->default('fixed'); // fixed, per_person, per_day, per_month

            // Features (JSON array of feature strings)
            $table->json('features')->nullable();

            // Additional details (JSON for flexible data)
            $table->json('specifications')->nullable(); // e.g., ["Processing Time: 7 days", "Validity: 6 months"]
            $table->json('inclusions')->nullable(); // What's included
            $table->json('exclusions')->nullable(); // What's not included

            // Display & Marketing
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('badge_text')->nullable(); // "Most Popular", "Best Value", "Premium"
            $table->string('badge_color')->default('blue'); // blue, green, purple, red
            $table->integer('sort_order')->default(0);

            // Limits & Restrictions
            $table->integer('max_bookings')->nullable(); // null = unlimited
            $table->integer('current_bookings')->default(0);
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['service_module_id', 'is_active']);
            $table->index('slug');
            $table->index('is_popular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
