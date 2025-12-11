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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('image_url')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('cta_text')->default('Learn More');
            $table->enum('placement', ['sidebar', 'inline', 'empty_state', 'banner', 'sticky_bottom', 'modal'])->default('sidebar');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->integer('priority')->default(0);
            $table->enum('status', ['draft', 'active', 'paused', 'expired'])->default('draft');
            $table->json('meta')->nullable(); // targeting rules: pages, roles, devices
            $table->bigInteger('impressions')->default(0);
            $table->bigInteger('clicks')->default(0);
            $table->decimal('ctr', 5, 2)->default(0); // click-through rate
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['status', 'start_at', 'end_at']);
            $table->index('placement');
            $table->index('priority');
        });

        Schema::create('ad_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('page')->nullable();
            $table->string('device_type')->nullable(); // desktop, tablet, mobile
            $table->string('user_agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at');

            $table->index(['ad_id', 'created_at']);
            $table->index('user_id');
        });

        Schema::create('ad_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('page')->nullable();
            $table->string('device_type')->nullable();
            $table->string('user_agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at');

            $table->index(['ad_id', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_clicks');
        Schema::dropIfExists('ad_impressions');
        Schema::dropIfExists('ads');
    }
};
