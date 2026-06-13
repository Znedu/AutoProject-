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
        Schema::create('service_stage_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')
                ->constrained('job_orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('service_stage_id')
                ->constrained('service_stages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_current')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['job_order_id', 'service_stage_id']);
            $table->index(['job_order_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_stage_progress');
    }
};
