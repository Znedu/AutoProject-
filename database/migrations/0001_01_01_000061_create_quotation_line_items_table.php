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
        Schema::create('quotation_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('description');
            $table->string('brand_preference')->nullable();
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_min', 12, 2);
            $table->decimal('unit_max', 12, 2);
            $table->decimal('unit_final', 12, 2)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['quotation_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_line_items');
    }
};
