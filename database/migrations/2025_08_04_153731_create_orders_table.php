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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('guest_email')->nullable();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->integer('subtotal'); // in cents
            $table->integer('tax_amount')->default(0); // in cents
            $table->integer('shipping_amount')->default(0); // in cents
            $table->integer('discount_amount')->default(0); // in cents
            $table->integer('total_amount'); // in cents
            $table->string('currency', 3)->default('USD');
            $table->json('billing_address');
            $table->json('shipping_address');
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable(); // 'aba_bank', 'cash_on_delivery', etc.
            $table->string('payment_reference')->nullable(); // ABA Bank transaction reference
            $table->string('aba_transaction_id')->nullable(); // ABA Bank specific transaction ID
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index('payment_status');
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
