<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, convert existing decimal prices to cents (multiply by 100)
        DB::statement('UPDATE products SET price = ROUND(price * 100) WHERE price IS NOT NULL');
        DB::statement('UPDATE products SET compare_price = ROUND(compare_price * 100) WHERE compare_price IS NOT NULL');
        DB::statement('UPDATE products SET cost_price = ROUND(cost_price * 100) WHERE cost_price IS NOT NULL');

        // Convert product_variants table as well
        DB::statement('UPDATE product_variants SET price = ROUND(price * 100) WHERE price IS NOT NULL');
        DB::statement('UPDATE product_variants SET compare_price = ROUND(compare_price * 100) WHERE compare_price IS NOT NULL');
        DB::statement('UPDATE product_variants SET cost_price = ROUND(cost_price * 100) WHERE cost_price IS NOT NULL');

        // Now change the column types to integer
        Schema::table('products', function (Blueprint $table) {
            $table->integer('price')->change();
            $table->integer('compare_price')->nullable()->change();
            $table->integer('cost_price')->nullable()->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('price')->nullable()->change();
            $table->integer('compare_price')->nullable()->change();
            $table->integer('cost_price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to decimal and divide by 100
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
            $table->decimal('compare_price', 10, 2)->nullable()->change();
            $table->decimal('cost_price', 10, 2)->nullable()->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->decimal('compare_price', 10, 2)->nullable()->change();
            $table->decimal('cost_price', 10, 2)->nullable()->change();
        });

        // Convert cents back to dollars (divide by 100)
        DB::statement('UPDATE products SET price = price / 100 WHERE price IS NOT NULL');
        DB::statement('UPDATE products SET compare_price = compare_price / 100 WHERE compare_price IS NOT NULL');
        DB::statement('UPDATE products SET cost_price = cost_price / 100 WHERE cost_price IS NOT NULL');

        DB::statement('UPDATE product_variants SET price = price / 100 WHERE price IS NOT NULL');
        DB::statement('UPDATE product_variants SET compare_price = compare_price / 100 WHERE compare_price IS NOT NULL');
        DB::statement('UPDATE product_variants SET cost_price = cost_price / 100 WHERE cost_price IS NOT NULL');
    }
};
