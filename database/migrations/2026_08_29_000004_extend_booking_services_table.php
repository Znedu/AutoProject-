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
        Schema::table('booking_services', function (Blueprint $table) {
            $table->decimal('unit_min_snapshot', 12, 2)->nullable()->after('preferred_brand');
            $table->decimal('unit_max_snapshot', 12, 2)->nullable()->after('unit_min_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_services', function (Blueprint $table) {
            $table->dropColumn(['unit_min_snapshot', 'unit_max_snapshot']);
        });
    }
};
