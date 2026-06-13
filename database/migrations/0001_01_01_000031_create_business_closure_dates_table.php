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
        Schema::create('business_closure_dates', function (Blueprint $table) {
            $table->id();
            $table->date('closure_date')->unique();
            $table->string('reason')->nullable();
            $table->boolean('is_recurring_rule')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_closure_dates');
    }
};
