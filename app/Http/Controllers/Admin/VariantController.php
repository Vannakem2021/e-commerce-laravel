<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantRequest;
use App\Http\Requests\Admin\UpdateVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VariantController extends Controller
{
    /**
     * Display a listing of variants for a product.
     */
    public function index(Product $product, Request $request): Response
    {
        $query = $product->variants();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->get('stock_status'));
        }

        // Sort
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        if (in_array($sortBy, ['name', 'sku', 'price', 'stock_quantity', 'sort_order', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->ordered();
        }

        $variants = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/variants/Index', [
            'product' => $product->load(['brand', 'categories']),
            'variants' => $variants,
            'filters' => $request->only(['search', 'active', 'stock_status', 'sort', 'direction']),
            'stockStatuses' => [
                'in_stock' => 'In Stock',
                'out_of_stock' => 'Out of Stock',
                'back_order' => 'Back Order',
            ],
        ]);
    }

    /**
     * Show the form for creating a new variant.
     */
    public function create(Product $product): Response
    {
        return Inertia::render('admin/variants/Create', [
            'product' => $product->load(['brand', 'categories']),
            'stockStatuses' => [
                'in_stock' => 'In Stock',
                'out_of_stock' => 'Out of Stock',
                'back_order' => 'Back Order',
            ],
        ]);
    }

    /**
     * Store a newly created variant.
     */
    public function store(StoreVariantRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $validated['product_id'] = $product->id;

        // Convert prices from dollars to cents
        if (isset($validated['price'])) {
            $validated['price'] = (int) ($validated['price'] * 100);
        }
        if (isset($validated['compare_price'])) {
            $validated['compare_price'] = (int) ($validated['compare_price'] * 100);
        }
        if (isset($validated['cost_price'])) {
            $validated['cost_price'] = (int) ($validated['cost_price'] * 100);
        }

        // Generate SKU if not provided
        if (empty($validated['sku'])) {
            $validated['sku'] = $this->generateVariantSku($product);
        }

        $variant = ProductVariant::create($validated);

        return redirect()
            ->route('admin.variants.index', $product)
            ->with('success', 'Variant created successfully.');
    }

    /**
     * Show the form for editing a variant.
     */
    public function edit(Product $product, ProductVariant $variant): Response
    {
        // Ensure variant belongs to product
        if ($variant->product_id !== $product->id) {
            abort(404);
        }

        return Inertia::render('admin/variants/Edit', [
            'product' => $product->load(['brand', 'categories']),
            'variant' => $variant,
            'stockStatuses' => [
                'in_stock' => 'In Stock',
                'out_of_stock' => 'Out of Stock',
                'back_order' => 'Back Order',
            ],
        ]);
    }

    /**
     * Update a variant.
     */
    public function update(UpdateVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        // Ensure variant belongs to product
        if ($variant->product_id !== $product->id) {
            abort(404);
        }

        $validated = $request->validated();

        // Convert prices from dollars to cents
        if (isset($validated['price'])) {
            $validated['price'] = (int) ($validated['price'] * 100);
        }
        if (isset($validated['compare_price'])) {
            $validated['compare_price'] = (int) ($validated['compare_price'] * 100);
        }
        if (isset($validated['cost_price'])) {
            $validated['cost_price'] = (int) ($validated['cost_price'] * 100);
        }

        $variant->update($validated);

        return redirect()
            ->route('admin.variants.index', $product)
            ->with('success', 'Variant updated successfully.');
    }

    /**
     * Remove a variant.
     */
    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        // Ensure variant belongs to product
        if ($variant->product_id !== $product->id) {
            abort(404);
        }

        $variant->delete();

        return redirect()
            ->route('admin.variants.index', $product)
            ->with('success', 'Variant deleted successfully.');
    }

    /**
     * Show bulk create form.
     */
    public function bulkCreate(Product $product): Response
    {
        return Inertia::render('admin/variants/BulkCreate', [
            'product' => $product->load(['brand', 'categories']),
            'stockStatuses' => [
                'in_stock' => 'In Stock',
                'out_of_stock' => 'Out of Stock',
                'back_order' => 'Back Order',
            ],
        ]);
    }



    /**
     * Generate a unique SKU for a variant.
     */
    private function generateVariantSku(Product $product): string
    {
        $baseSku = $product->sku;

        // Generate a simple suffix based on existing variant count
        $variantCount = $product->variants()->count();
        $sku = $baseSku . '-V' . ($variantCount + 1);

        // Ensure uniqueness
        $counter = 1;
        $originalSku = $sku;
        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }


}
