<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Tax rate (8% as per plan)
     */
    private const TAX_RATE = 0.08;

    /**
     * Shipping rate in cents ($9.99 as per plan)
     */
    private const SHIPPING_RATE = 999;

    /**
     * Free shipping threshold in cents ($50.00 as per plan)
     */
    private const FREE_SHIPPING_THRESHOLD = 5000;

    /**
     * Create an order from a cart.
     */
    public function createOrderFromCart(
        Cart $cart,
        array $billingAddress,
        array $shippingAddress,
        ?string $guestEmail = null,
        string $paymentMethod = 'aba_bank'
    ): Order {
        return DB::transaction(function () use ($cart, $billingAddress, $shippingAddress, $guestEmail, $paymentMethod) {
            // Validate cart has items
            if ($cart->items->isEmpty()) {
                throw new \Exception('Cannot create order from empty cart');
            }

            // Validate inventory for all items
            $this->validateInventory($cart);

            // Calculate totals
            $subtotal = $cart->total_price;
            $shippingAmount = $this->calculateShipping($subtotal);
            $taxAmount = $this->calculateTax($subtotal);
            $totalAmount = $subtotal + $shippingAmount + $taxAmount;

            // Create the order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $cart->user_id,
                'guest_email' => $guestEmail,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => 0, // TODO: Implement discounts in future
                'total_amount' => $totalAmount,
                'currency' => 'USD',
                'billing_address' => $billingAddress,
                'shipping_address' => $shippingAddress,
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
            ]);

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                OrderItem::createFromCartItem($cartItem, $order->id);
            }

            // Reduce inventory
            $this->reduceInventory($cart);

            // Mark cart as converted
            $cart->update(['status' => 'converted']);

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'user_id' => $order->user_id,
                'guest_email' => $order->guest_email,
            ]);

            return $order;
        });
    }

    /**
     * Calculate shipping amount based on subtotal.
     */
    public function calculateShipping(int $subtotal): int
    {
        if ($subtotal >= self::FREE_SHIPPING_THRESHOLD) {
            return 0;
        }

        return self::SHIPPING_RATE;
    }

    /**
     * Calculate tax amount based on subtotal.
     */
    public function calculateTax(int $subtotal): int
    {
        return (int) round($subtotal * self::TAX_RATE);
    }

    /**
     * Calculate order totals.
     */
    public function calculateTotals(Cart $cart): array
    {
        $subtotal = $cart->total_price;
        $shippingAmount = $this->calculateShipping($subtotal);
        $taxAmount = $this->calculateTax($subtotal);
        $totalAmount = $subtotal + $shippingAmount + $taxAmount;

        return [
            'subtotal' => $subtotal,
            'shipping_amount' => $shippingAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'formatted_subtotal' => '$' . number_format($subtotal / 100, 2),
            'formatted_shipping' => '$' . number_format($shippingAmount / 100, 2),
            'formatted_tax' => '$' . number_format($taxAmount / 100, 2),
            'formatted_total' => '$' . number_format($totalAmount / 100, 2),
        ];
    }

    /**
     * Validate inventory for all cart items.
     */
    private function validateInventory(Cart $cart): void
    {
        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;
            $variant = $cartItem->variant;

            // Check if product is still available
            if ($product->status !== 'published') {
                throw new \Exception("Product '{$product->name}' is no longer available");
            }

            // Check stock availability
            $availableStock = $variant ? $variant->stock_quantity : $product->stock_quantity;
            
            if ($cartItem->quantity > $availableStock) {
                $itemName = $variant ? "{$product->name} - {$variant->name}" : $product->name;
                throw new \Exception("Insufficient stock for '{$itemName}'. Only {$availableStock} available");
            }
        }
    }

    /**
     * Reduce inventory for all cart items.
     */
    private function reduceInventory(Cart $cart): void
    {
        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;
            $variant = $cartItem->variant;

            if ($variant) {
                // Reduce variant stock
                $variant->decrement('stock_quantity', $cartItem->quantity);
                
                // Update variant stock status if needed
                if ($variant->stock_quantity <= 0) {
                    $variant->update(['stock_status' => 'OUT_STOCK']);
                }
            } else {
                // Reduce product stock
                $product->decrement('stock_quantity', $cartItem->quantity);
                
                // Update product stock status if needed
                if ($product->stock_quantity <= 0) {
                    $product->update(['stock_status' => 'OUT_STOCK']);
                }
            }
        }
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Order $order, string $status): bool
    {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid order status: {$status}");
        }

        $updated = $order->update(['status' => $status]);

        if ($updated) {
            Log::info('Order status updated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $order->getOriginal('status'),
                'new_status' => $status,
            ]);
        }

        return $updated;
    }

    /**
     * Update payment status with ABA Bank details.
     */
    public function updatePaymentStatus(
        Order $order,
        string $paymentStatus,
        ?string $paymentReference = null,
        ?string $abaTransactionId = null
    ): bool {
        $validStatuses = ['pending', 'paid', 'failed', 'cancelled', 'refunded'];
        
        if (!in_array($paymentStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid payment status: {$paymentStatus}");
        }

        $updated = $order->updatePaymentStatus($paymentStatus, $paymentReference, $abaTransactionId);

        if ($updated && $paymentStatus === 'paid') {
            // Automatically move order to processing when payment is confirmed
            $this->updateOrderStatus($order, 'processing');
        }

        if ($updated) {
            Log::info('Payment status updated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_status' => $paymentStatus,
                'payment_reference' => $paymentReference,
                'aba_transaction_id' => $abaTransactionId,
            ]);
        }

        return $updated;
    }

    /**
     * Get order with all related data.
     */
    public function getOrderWithItems(int $orderId): ?Order
    {
        return Order::with(['items.product.brand', 'items.variant', 'user'])
            ->find($orderId);
    }

    /**
     * Cancel an order if possible.
     */
    public function cancelOrder(Order $order, ?string $reason = null): bool
    {
        if (!$order->canBeCancelled()) {
            throw new \Exception('Order cannot be cancelled in its current status');
        }

        return DB::transaction(function () use ($order, $reason) {
            // Restore inventory
            $this->restoreInventory($order);

            // Update order status
            $updated = $order->markAsCancelled();

            if ($reason) {
                $order->update(['notes' => ($order->notes ? $order->notes . "\n" : '') . "Cancelled: {$reason}"]);
            }

            if ($updated) {
                Log::info('Order cancelled', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'reason' => $reason,
                ]);
            }

            return $updated;
        });
    }

    /**
     * Restore inventory when order is cancelled.
     */
    private function restoreInventory(Order $order): void
    {
        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;
            $variant = $orderItem->variant;

            if ($variant) {
                // Restore variant stock
                $variant->increment('stock_quantity', $orderItem->quantity);
                
                // Update variant stock status if needed
                if ($variant->stock_quantity > 0 && $variant->stock_status === 'OUT_STOCK') {
                    $variant->update(['stock_status' => 'IN_STOCK']);
                }
            } else {
                // Restore product stock
                $product->increment('stock_quantity', $orderItem->quantity);
                
                // Update product stock status if needed
                if ($product->stock_quantity > 0 && $product->stock_status === 'OUT_STOCK') {
                    $product->update(['stock_status' => 'IN_STOCK']);
                }
            }
        }
    }
}
