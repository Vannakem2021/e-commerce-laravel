<?php

// Removed AttributeController import
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VariantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here are the admin routes for the e-commerce application.
| All routes require authentication and admin role.
|
*/

// All admin routes require authentication, verification, and admin role
Route::middleware(['auth', 'verified', 'admin'])->group(function () {

    // Admin Dashboard
    Route::get('admin', function () {
        return Inertia::render('admin/Dashboard');
    })->name('admin.dashboard');

    // Admin Pages
    Route::prefix('admin')->name('admin.')->group(function () {
        // Product Management
        Route::resource('products', ProductController::class);

        // Product image management routes
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.delete');
        Route::post('products/{product}/images/reorder', [ProductController::class, 'reorderImages'])->name('products.images.reorder');
        Route::post('products/{product}/images/{image}/primary', [ProductController::class, 'setPrimaryImage'])->name('products.images.primary');

        // Category Management - use ID binding for admin routes
        Route::resource('categories', CategoryController::class)->parameters([
            'categories' => 'category:id'
        ]);

        // Category bulk operations
        Route::post('categories/bulk-status', [CategoryController::class, 'bulkUpdateStatus'])->name('categories.bulk-status');
        Route::delete('categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
        Route::post('categories/sort-order', [CategoryController::class, 'updateSortOrder'])->name('categories.sort-order');

        // Brand Management - use ID binding for admin routes
        Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class)->parameters([
            'brands' => 'brand:id'
        ]);

        // Brand bulk operations
        Route::post('brands/bulk-status', [\App\Http\Controllers\Admin\BrandController::class, 'bulkUpdateStatus'])->name('brands.bulk-status');
        Route::delete('brands/bulk-delete', [\App\Http\Controllers\Admin\BrandController::class, 'bulkDelete'])->name('brands.bulk-delete');
        Route::post('brands/sort-order', [\App\Http\Controllers\Admin\BrandController::class, 'updateSortOrder'])->name('brands.sort-order');

        // Removed Attribute Management routes

        // Variant Management
        Route::get('products/{product}/variants', [VariantController::class, 'index'])->name('variants.index');
        Route::get('products/{product}/variants/create', [VariantController::class, 'create'])->name('variants.create');
        Route::post('products/{product}/variants', [VariantController::class, 'store'])->name('variants.store');
        Route::get('products/{product}/variants/{variant}/edit', [VariantController::class, 'edit'])->name('variants.edit');
        Route::patch('products/{product}/variants/{variant}', [VariantController::class, 'update'])->name('variants.update');
        Route::delete('products/{product}/variants/{variant}', [VariantController::class, 'destroy'])->name('variants.destroy');


        Route::get('orders', function () {
            return Inertia::render('admin/Orders');
        })->name('orders.index');

        Route::get('users', function () {
            return Inertia::render('admin/Users');
        })->name('users.index');

        Route::get('reports', function () {
            return Inertia::render('admin/Reports');
        })->name('reports.index');

        Route::get('settings', function () {
            return Inertia::render('admin/Settings');
        })->name('settings.index');
    });
});
