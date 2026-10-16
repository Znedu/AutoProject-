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
        Schema::create('paint_panels', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('group'); // front, doors, rear_side, other
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group', 'sort_order']);
            $table->index('is_active');
        });

        Schema::create('booking_paint_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();
            $table->string('scope_mode'); // touch_up, custom_paint, body_repair, full_build
            $table->string('vehicle_size_class')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('scope_mode');
        });

        Schema::create('booking_paint_panels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();
            $table->string('panel_code');
            $table->string('severity')->nullable(); // light_scratch, paint_only, dent, body_work, replace_panel
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'panel_code']);
            $table->index('panel_code');
        });

        Schema::create('booking_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();
            $table->string('type')->default('damage_photo');
            $table->string('disk')->default('public');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['booking_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_attachments');
        Schema::dropIfExists('booking_paint_panels');
        Schema::dropIfExists('booking_paint_scopes');
        Schema::dropIfExists('paint_panels');
    }
};
