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
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->integer('world_ranking')->nullable();
            $table->integer('country_ranking')->nullable();
            $table->decimal('tuition_min', 12, 2)->nullable(); // USD per year
            $table->decimal('tuition_max', 12, 2)->nullable(); // USD per year
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->integer('established_year')->nullable();
            $table->enum('type', ['public', 'private'])->default('public');
            $table->decimal('acceptance_rate', 5, 2)->nullable(); // Percentage
            $table->integer('student_count')->nullable();
            $table->integer('international_students')->nullable();
            $table->boolean('scholarships_available')->default(false);
            $table->decimal('application_fee', 10, 2)->nullable(); // USD
            $table->text('description')->nullable();
            $table->text('programs')->nullable(); // JSON array of programs
            $table->text('popular_majors')->nullable(); // JSON array
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('country_id');
            $table->index('world_ranking');
            $table->index('is_featured');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
