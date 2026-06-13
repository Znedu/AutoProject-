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
        Schema::create('appointment_slot_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedSmallInteger('slot_duration_minutes')->default(60);
            $table->unsignedSmallInteger('max_capacity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['day_of_week', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_slot_configs');
    }
};
