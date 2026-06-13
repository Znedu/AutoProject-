<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('roles')->count() === 0) {
            (new RolePermissionSeeder)->run();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('phone')
                ->constrained('roles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        if (Schema::hasColumn('users', 'role')) {
            $roleMap = DB::table('roles')->pluck('id', 'slug');

            DB::table('users')
                ->whereNotNull('role')
                ->orderBy('id')
                ->chunkById(100, function ($users) use ($roleMap): void {
                    foreach ($users as $user) {
                        $roleId = $roleMap[$user->role] ?? $roleMap['customer'] ?? null;

                        if ($roleId !== null) {
                            DB::table('users')
                                ->where('id', $user->id)
                                ->update(['role_id' => $roleId]);
                        }
                    }
                });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        $customerRoleId = DB::table('roles')->where('slug', 'customer')->value('id');

        if ($customerRoleId !== null) {
            DB::table('users')
                ->whereNull('role_id')
                ->update(['role_id' => $customerRoleId]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->after('phone');
        });

        $roleMap = DB::table('roles')->pluck('slug', 'id');

        DB::table('users')
            ->whereNotNull('role_id')
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($roleMap): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['role' => $roleMap[$user->role_id] ?? 'customer']);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
