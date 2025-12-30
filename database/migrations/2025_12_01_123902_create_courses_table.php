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
        if (Schema::hasTable('courses')) {
            return;
        }
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained()->onDelete('cascade');
            $table->string('code', 50)->unique(); // Course code (e.g., CS101, MBA502)
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject'); // Computer Science, Business, Engineering, etc.
            $table->string('level'); // Undergraduate, Graduate, Postgraduate, Certificate, Diploma
            $table->string('degree_type')->nullable(); // Bachelor's, Master's, PhD, MBA, etc.
            $table->integer('duration_months'); // Course duration in months
            $table->string('study_mode'); // Full-time, Part-time, Online, Hybrid
            $table->decimal('tuition_fee', 10, 2)->nullable(); // Annual tuition fee
            $table->string('currency', 3)->default('USD');
            $table->date('start_date')->nullable();
            $table->date('application_deadline')->nullable();
            $table->integer('credits')->nullable(); // Credit hours
            $table->json('prerequisites')->nullable(); // Array of prerequisite courses
            $table->json('learning_outcomes')->nullable(); // Array of learning outcomes
            $table->string('language', 50)->default('English');
            $table->integer('enrollment_capacity')->nullable();
            $table->integer('current_enrollment')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('reviews_count')->default(0);
            $table->boolean('scholarships_available')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('university_id');
            $table->index('subject');
            $table->index('level');
            $table->index('study_mode');
            $table->index('is_featured');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
