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
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->default('General Parts')->change();
            $table->decimal('cost_price', 12, 2)->default(0.00)->after('unit_price');
            $table->integer('stock_quantity')->default(0)->after('cost_price');
            $table->integer('min_stock_threshold')->default(5)->after('stock_quantity');
            $table->string('location')->nullable()->after('min_stock_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'stock_quantity', 'min_stock_threshold', 'location']);
            $table->enum('category', ['product', 'material'])->default('product')->change();
        });
    }
};
