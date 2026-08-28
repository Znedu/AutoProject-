<?php

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
        Schema::table('quotation_line_items', function (Blueprint $table) {
            $table->string('item_type')->default('service')->after('service_id');
            $table->foreignId('product_id')
                ->nullable()
                ->after('service_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('sku')->nullable()->after('product_id');
            $table->text('notes')->nullable()->after('brand_preference');
            $table->decimal('line_total', 12, 2)->nullable()->after('unit_final');
            $table->string('source')->nullable()->after('line_total');
            $table->foreignId('added_by')
                ->nullable()
                ->after('source')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index('item_type');
        });

        DB::table('quotation_line_items')->update([
            'item_type' => 'service',
            'source' => 'initial_estimate',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_line_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['added_by']);
            $table->dropIndex(['item_type']);
            $table->dropColumn([
                'item_type',
                'product_id',
                'sku',
                'notes',
                'line_total',
                'source',
                'added_by',
            ]);
        });
    }
};
