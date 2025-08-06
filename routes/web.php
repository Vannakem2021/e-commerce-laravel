<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $featuredProducts = \App\Models\Product::with(['brand', 'primaryImage', 'images', 'categories'])
        ->where('status', 'published')
        ->where('is_featured', true)
        ->orderBy('sort_order')
        ->take(8)
        ->get();

    $bestSellers = \App\Models\Product::with(['brand', 'primaryImage', 'images', 'categories'])
        ->where('status', 'published')
        ->where('is_on_sale', true)
        ->orderBy('sort_order')
        ->take(8)
        ->get();

    $newArrivals = \App\Models\Product::with(['brand', 'primaryImage', 'images', 'categories'])
        ->where('status', 'published')
        ->where('created_at', '>=', now()->subDays(30))
        ->orderBy('created_at', 'desc')
        ->take(8)
        ->get();

    return Inertia::render('Home', [
        'featuredProducts' => $featuredProducts,
        'bestSellers' => $bestSellers,
        'newArrivals' => $newArrivals,
    ]);
})->name('home');

// Product routes - specific routes must come before parameterized routes
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/featured', [ProductController::class, 'featured'])->name('products.featured');
Route::get('/products/on-sale', [ProductController::class, 'onSale'])->name('products.on-sale');
Route::get('/products/new-arrivals', [ProductController::class, 'newArrivals'])->name('products.new-arrivals');
// Product detail route - must be last to avoid conflicts
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Removed attribute-related API routes

// API Routes for categories (DEPRECATED - Use shared Inertia data instead)
Route::get('/api/categories', function () {
    // Log deprecation warning
    if (config('features.log_deprecated_api_usage', true)) {
        \Illuminate\Support\Facades\Log::warning('Deprecated API route accessed: /api/categories', [
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'referer' => request()->header('referer'),
        ]);
    }

    // Return categories with deprecation notice
    $categories = app(\App\Http\Controllers\ProductController::class)->getCategories();

    return response()->json([
        ...$categories->getData(true),
        'deprecated' => true,
        'message' => 'This endpoint is deprecated. Use shared Inertia data instead.',
        'migration_guide' => 'Access categories via usePage().props.shared.categories',
    ]);
})->name('api.categories.deprecated');

Route::get('/api/categories/featured', function () {
    // Log deprecation warning
    if (config('features.log_deprecated_api_usage', true)) {
        \Illuminate\Support\Facades\Log::warning('Deprecated API route accessed: /api/categories/featured', [
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'referer' => request()->header('referer'),
        ]);
    }

    // Return featured categories with deprecation noti
    $categories = app(\App\Http\Controllers\ProductController::class)->getFeaturedCategories();

    return response()->json([
        ...$categories->getData(true),
        'deprecated' => true,
        'message' => 'This endpoint is deprecated. Use shared Inertia data instead.',
        'migration_guide' => 'Access featured categories via usePage().props.shared.featured_categories',
    ]);
})->name('api.categories.featured.deprecated');

// Debug route for testing Phase 1 implementation
Route::get('/debug', function () {
    return Inertia::render('Debug');
})->name('debug');

// Test route to debug BrandService issue
Route::get('/test-brands', function () {
    try {
        $brandService = app(\App\Services\BrandService::class);
        $brands = $brandService->getBrandsForFiltering();

        return response()->json([
            'success' => true,
            'brands_count' => $brands->count(),
            'brands' => $brands->take(5), // Show first 5 brands
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
})->name('test-brands');

// Test route to debug products listing page issue
Route::get('/test-products', function () {
    try {
        $products = \App\Models\Product::with(['brand', 'categories', 'primaryImage', 'images'])
            ->where('status', 'published')
            ->paginate(12);

        $brandService = app(\App\Services\BrandService::class);
        $brands = $brandService->getBrandsForFiltering();

        $categories = \App\Models\Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('Products', [
            'products' => $products,
            'filters' => [],
            'brands' => $brands,
            'categories' => $categories,
            'priceRange' => ['min' => 0, 'max' => 1000],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
})->name('test-products');

// Public category browsing routes
Route::get('/categories', [ProductController::class, 'categories'])->name('categories.index');
Route::get('/categories/{category:slug}', [ProductController::class, 'categoryProducts'])->name('categories.show');

// Legacy routes for backward compatibility
Route::get('/products/new', function () {
    return redirect()->route('products', ['sort' => 'newest']);
})->name('products.new');

Route::get('/products/bestsellers', function () {
    return redirect()->route('products', ['featured' => 1]);
})->name('products.bestsellers');

// Brand routes
Route::get('/brands', [App\Http\Controllers\BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{slug}', [App\Http\Controllers\BrandController::class, 'show'])->name('brands.show');
Route::get('/api/brands/search', [App\Http\Controllers\BrandController::class, 'search'])->name('brands.search');

Route::get('/best-sellers', function () {
    $products = \App\Models\Product::with(['brand', 'primaryImage', 'images', 'categories'])
        ->where('status', 'published')
        ->where('is_on_sale', true)
        ->orderBy('sort_order')
        ->take(20)
        ->get();

    return Inertia::render('BestSellers', [
        'products' => $products,
    ]);
})->name('best-sellers');

// Legacy product detail route
Route::get('/product/{slug}', function ($slug) {
    return redirect()->route('products.show', $slug);
})->name('product.detail');

// Cart routes
Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');

// Checkout routes
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');

// Rate-limited cart modification routes
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/cart', [App\Http\Controllers\CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart', [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
    Route::put('/cart/{cartItem}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy');
});

// Address API routes
Route::prefix('api/addresses')->group(function () {
    Route::get('/provinces', [App\Http\Controllers\AddressController::class, 'getProvinces']);
    Route::get('/districts', [App\Http\Controllers\AddressController::class, 'getDistricts']);
    Route::get('/communes', [App\Http\Controllers\AddressController::class, 'getCommunes']);
    Route::get('/postal-code', [App\Http\Controllers\AddressController::class, 'getPostalCode']);
    Route::get('/search', [App\Http\Controllers\AddressController::class, 'searchAreas']);
});

// Authenticated address management routes
Route::middleware('auth')->prefix('api/addresses')->group(function () {
    Route::post('/', [App\Http\Controllers\AddressController::class, 'store']);
    Route::put('/{address}', [App\Http\Controllers\AddressController::class, 'update']);
    Route::delete('/{address}', [App\Http\Controllers\AddressController::class, 'destroy']);
});

// Dashboard route removed - admin dashboard is now at /admin

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';

