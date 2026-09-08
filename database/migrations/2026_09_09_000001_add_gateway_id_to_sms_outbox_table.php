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
        Schema::table('sms_outbox', function (Blueprint $table): void {
            $table->string('gateway_id')->nullable()->after('status');
            $table->text('error_message')->nullable()->after('gateway_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_outbox', function (Blueprint $table): void {
            $table->dropColumn(['gateway_id', 'error_message']);
        });
    }
};
