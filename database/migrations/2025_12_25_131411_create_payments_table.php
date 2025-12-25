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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['bkash', 'nagad', 'rocket', 'bank_transfer', 'cash', 'card', 'check', 'other'])->default('cash');
            $table->string('transaction_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_id', 'payment_date']);
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
