<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
// Removed ProductAttribute import
use App\Models\ProductImage;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductTag;
use App\Services\ProductImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Product::with(['brand', 'categories', 'primaryImage'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->brand_id, function ($query, $brandId) {
                $query->where('brand_id', $brandId);
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                });
            });

        // Get per_page from request, default to 10, max 100
        $perPage = min((int) $request->get('per_page', 10), 100);
        $products = $query->latest()->paginate($perPage)->withQueryString();

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'filters' => $request->only(['search', 'status', 'brand_id', 'category_id']),
            'brands' => Brand::active()->ordered()->get(['id', 'name']),
            'categories' => Category::active()->root()->ordered()->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/products/Create', [
            'brands' => Brand::active()->ordered()->get(['id', 'name']),
            'categories' => Category::active()->ordered()->get(['id', 'name', 'parent_id']),
            'tags' => ProductTag::all(['id', 'name', 'color']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, ProductImageService $imageService): RedirectResponse
    {
        $validated = $request->getValidatedDataWithPriceConversion();

        // Set user_id to current authenticated user
        $validated['user_id'] = Auth::id();

        // Set published_at if status is published and no date provided
        if ($validated['status'] === 'published' && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Extract relationships data
        $categories = $validated['categories'] ?? [];
        $primaryCategoryId = $validated['primary_category_id'] ?? null;
        $tags = $validated['tags'] ?? [];

        // Remove relationship data from main array
        unset($validated['categories'], $validated['primary_category_id'], $validated['tags']);

        $product = Product::create($validated);

        // Attach categories
        if (!empty($categories)) {
            $categoryData = [];
            foreach ($categories as $categoryId) {
                $categoryData[$categoryId] = ['is_primary' => $categoryId == $primaryCategoryId];
            }
            $product->categories()->attach($categoryData);
        }

        // Attach tags
        if (!empty($tags)) {
            $product->tags()->attach($tags);
        }

        // Removed attribute handling

        // Handle image uploads
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $imageMetadata = $request->input('image_metadata', []);

            try {
                $imageService->uploadImages($product, $files, $imageMetadata);
            } catch (\Exception $e) {
                // Log error but don't fail the product creation
                logger()->error('Failed to upload product images', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('status', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): Response
    {
        $product->load([
            'brand',
            'categories',
            'images' => function ($query) {
                $query->ordered();
            },
            'variants',
            'tags',
            'user'
        ]);

        return Inertia::render('admin/products/Show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Response
    {
        $product->load(['brand', 'categories', 'images', 'tags', 'variants']);

        return Inertia::render('admin/products/Edit', [
            'product' => $product,
            'brands' => Brand::active()->ordered()->get(['id', 'name']),
            'categories' => Category::active()->ordered()->get(['id', 'name', 'parent_id']),
            'tags' => ProductTag::all(['id', 'name', 'color']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product, ProductImageService $imageService): RedirectResponse
    {
        $validated = $request->getValidatedDataWithPriceConversion();

        // Set published_at if status is published and no date provided
        if ($validated['status'] === 'published' && !$product->published_at && !isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Extract relationships data
        $categories = $validated['categories'] ?? [];
        $primaryCategoryId = $validated['primary_category_id'] ?? null;
        $tags = $validated['tags'] ?? [];

        // Remove relationship data from main array
        unset($validated['categories'], $validated['primary_category_id'], $validated['tags']);

        $product->update($validated);

        // Sync categories
        if (!empty($categories)) {
            $categoryData = [];
            foreach ($categories as $categoryId) {
                $categoryData[$categoryId] = ['is_primary' => $categoryId == $primaryCategoryId];
            }
            $product->categories()->sync($categoryData);
        } else {
            $product->categories()->detach();
        }

        // Sync tags
        $product->tags()->sync($tags);

        // Removed attribute handling

        // Handle image deletions
        if ($request->has('images_to_delete') && !empty($request->input('images_to_delete'))) {
            $imagesToDelete = $request->input('images_to_delete');
            foreach ($imagesToDelete as $imageId) {
                $image = $product->images()->find($imageId);
                if ($image) {
                    // Delete the physical file and database record
                    $imageService->deleteImage($image);
                }
            }
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $imageMetadata = $request->input('image_metadata', []);

            try {
                $imageService->uploadImages($product, $files, $imageMetadata);
            } catch (\Exception $e) {
                logger()->error('Failed to upload product images during update', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Handle existing images updates
        if ($request->has('existing_images')) {
            $existingImages = json_decode($request->input('existing_images'), true);
            foreach ($existingImages as $imageData) {
                if (isset($imageData['id'])) {
                    $product->images()->where('id', $imageData['id'])->update([
                        'alt_text' => $imageData['alt_text'] ?? null,
                        'is_primary' => $imageData['is_primary'] ?? false,
                        'sort_order' => $imageData['sort_order'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')
            ->with('status', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('status', 'Product deleted successfully.');
    }

    /**
     * Delete a product image
     */
    public function deleteImage(Product $product, ProductImage $image, ProductImageService $imageService): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['error' => 'Image does not belong to this product'], 403);
        }

        $success = $imageService->deleteImage($image);

        if ($success) {
            return response()->json(['message' => 'Image deleted successfully']);
        }

        return response()->json(['error' => 'Failed to delete image'], 500);
    }

    /**
     * Reorder product images
     */
    public function reorderImages(Product $product, Request $request, ProductImageService $imageService): JsonResponse
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:product_images,id',
        ]);

        $imageService->reorderImages($product, $request->input('image_ids'));

        return response()->json(['message' => 'Images reordered successfully']);
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage(Product $product, ProductImage $image, ProductImageService $imageService): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['error' => 'Image does not belong to this product'], 403);
        }

        $imageService->setPrimaryImage($product, $image->id);

        return response()->json(['message' => 'Primary image set successfully']);
    }
}
