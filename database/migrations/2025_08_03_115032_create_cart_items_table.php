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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->integer('price')->comment('Price in cents at time of adding to cart');
            $table->json('product_snapshot')->nullable()->comment('Product data snapshot for historical accuracy');
            $table->json('variant_attributes')->nullable()->comment('Selected variant attributes');
            $table->timestamps();

            // Unique constraint to prevent duplicate items
            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'cart_product_variant_unique');

            // Indexes
            $table->index(['cart_id', 'product_id']);
            $table->index(['product_id', 'quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
