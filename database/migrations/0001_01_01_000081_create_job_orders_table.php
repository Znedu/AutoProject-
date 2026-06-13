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
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->foreignId('booking_id')
                ->unique()
                ->constrained('bookings')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('mechanic_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->string('status')->default('pending');
            $table->string('priority')->default('medium');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->date('estimated_completion_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['mechanic_id', 'status']);
            $table->index(['status', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
