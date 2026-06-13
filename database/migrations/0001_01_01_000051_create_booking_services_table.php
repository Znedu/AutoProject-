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
        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('preferred_brand')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_services');
    }
};
