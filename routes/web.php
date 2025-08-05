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

// API Routes for categories
Route::get('/api/categories', [ProductController::class, 'getCategories'])->name('api.categories');
Route::get('/api/categories/featured', [ProductController::class, 'getFeaturedCategories'])->name('api.categories.featured');

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

Route::get('/brands', function () {
    $brands = \App\Models\Brand::where('is_active', true)
        ->withCount(['products' => function ($query) {
            $query->where('status', 'published');
        }])
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    return Inertia::render('Brands', [
        'brands' => $brands,
    ]);
})->name('brands');

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
Route::get('/cart/data', [App\Http\Controllers\CartController::class, 'data'])->name('cart.data');
Route::get('/cart/summary', [App\Http\Controllers\CartController::class, 'summary'])->name('cart.summary');
Route::get('/cart/validate', [App\Http\Controllers\CartController::class, 'validate'])->name('cart.validate');

// Checkout routes
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');

// Rate-limited cart modification routes
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/cart', [App\Http\Controllers\CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart', [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
    Route::put('/cart/{cartItem}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy');
});

// Dashboard route removed - admin dashboard is now at /admin

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';

