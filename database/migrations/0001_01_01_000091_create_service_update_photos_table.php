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
        Schema::create('service_update_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_update_id')
                ->constrained('service_updates')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('disk')->default('local');
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['service_update_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_update_photos');
    }
};
