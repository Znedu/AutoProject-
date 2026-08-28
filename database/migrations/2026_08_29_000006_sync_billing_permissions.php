<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new RolePermissionSeeder)->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to preserve role mappings
    }
};
