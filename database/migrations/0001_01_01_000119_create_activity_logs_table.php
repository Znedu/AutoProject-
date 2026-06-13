<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * General-purpose audit trail for administrator oversight.
     * Append-only: no updated_at, no soft deletes.
     * Covers logins, CRUD on users/services, payment verifications,
     * and any other auditable actions beyond booking status changes.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('action');                       // login, created, updated, deleted, approved, verified, exported
            $table->nullableMorphs('subject');              // subject_type + subject_id + index
            $table->text('description')->nullable();        // Human-readable summary
            $table->json('properties')->nullable();         // Before/after diff, extra context
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();  // Immutable log time

            $table->index(['user_id', 'created_at']);
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
