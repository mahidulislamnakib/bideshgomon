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
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // VAT, Service Tax, etc.
            $table->string('code')->unique(); // VAT, ST, etc.
            $table->decimal('rate', 5, 2); // 15.00 for 15%
            $table->text('description')->nullable();
            $table->boolean('is_compound')->default(false); // Tax on tax
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('applicable_services')->nullable(); // Which services this tax applies to
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};
