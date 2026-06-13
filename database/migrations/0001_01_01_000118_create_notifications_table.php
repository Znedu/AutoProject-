<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Standard Laravel notifications table supporting polymorphic
     * notifiable (users). Stores in-app notifications for the
     * notification bell visible in all dashboard layouts.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');                   // notifiable_type + notifiable_id + index
            $table->json('data');                           // title, message, action_url, icon
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
