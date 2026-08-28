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
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('services_subtotal', 12, 2)->nullable()->after('final_total');
            $table->decimal('products_subtotal', 12, 2)->nullable()->after('services_subtotal');
            $table->decimal('labor_subtotal', 12, 2)->nullable()->after('products_subtotal');
            $table->decimal('discounts_total', 12, 2)->nullable()->after('labor_subtotal');
            $table->decimal('fees_subtotal', 12, 2)->nullable()->after('discounts_total');
            $table->decimal('amount_paid_snapshot', 12, 2)->nullable()->after('fees_subtotal');
            $table->decimal('balance_due_snapshot', 12, 2)->nullable()->after('amount_paid_snapshot');
            $table->timestamp('finalized_at')->nullable()->after('approved_at');
            $table->foreignId('finalized_by')
                ->nullable()
                ->after('finalized_at')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['finalized_by']);
            $table->dropColumn([
                'services_subtotal',
                'products_subtotal',
                'labor_subtotal',
                'discounts_total',
                'fees_subtotal',
                'amount_paid_snapshot',
                'balance_due_snapshot',
                'finalized_at',
                'finalized_by',
            ]);
        });
    }
};
