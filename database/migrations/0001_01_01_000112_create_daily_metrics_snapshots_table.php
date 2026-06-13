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
        Schema::create('daily_metrics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date')->unique();
            $table->unsignedInteger('total_bookings')->default(0);
            $table->unsignedInteger('completed_bookings')->default(0);
            $table->decimal('total_revenue', 14, 2)->default(0);
            $table->unsignedInteger('new_customers')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('metric_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_metrics_snapshots');
    }
};
