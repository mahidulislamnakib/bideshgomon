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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('frequency'); // weekly, bi-weekly, monthly, quarterly, semi-annual, annual
            $table->integer('frequency_interval')->default(1); // Every X frequency
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_invoice_date');
            $table->integer('day_of_month')->nullable(); // For monthly invoices
            $table->integer('total_occurrences')->nullable();
            $table->integer('occurrences_generated')->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->json('items'); // Store line items
            $table->string('status')->default('active'); // active, paused, completed, cancelled
            $table->boolean('send_automatically')->default(true);
            $table->integer('days_before_due')->default(7); // Generate invoice X days before due
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'next_invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};
