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
        Schema::table('service_brands', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0.00)->after('name');
            $table->string('short_description', 255)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_brands', function (Blueprint $table) {
            $table->dropColumn(['price', 'short_description']);
        });
    }
};
