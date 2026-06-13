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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('type');
            $table->string('status')->default('draft');
            $table->decimal('min_total', 12, 2);
            $table->decimal('max_total', 12, 2);
            $table->decimal('final_total', 12, 2)->nullable();
            $table->char('currency', 3)->default('PHP');
            $table->text('notes')->nullable();
            $table->foreignId('prepared_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'version']);
            $table->index(['booking_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
