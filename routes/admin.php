<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here are the admin routes for the e-commerce application.
| All routes require authentication and appropriate permissions.
|
*/

// Admin Dashboard - requires admin role
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('admin', function () {
        return Inertia::render('admin/Dashboard');
    })->name('admin.dashboard');
});

// Product Management - requires product permissions
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // Products
    Route::middleware('ensure.permission:view-products')->group(function () {
        Route::get('products', function () {
            return Inertia::render('admin/products/Index');
        })->name('products.index');
        
        Route::get('products/{id}', function ($id) {
            return Inertia::render('admin/products/Show', ['id' => $id]);
        })->name('products.show');
    });
    
    Route::middleware('ensure.permission:create-products')->group(function () {
        Route::get('products/create', function () {
            return Inertia::render('admin/products/Create');
        })->name('products.create');
        
        Route::post('products', function () {
            // Handle product creation
            return redirect()->route('admin.products.index');
        })->name('products.store');
    });
    
    Route::middleware('ensure.permission:edit-products')->group(function () {
        Route::get('products/{id}/edit', function ($id) {
            return Inertia::render('admin/products/Edit', ['id' => $id]);
        })->name('products.edit');
        
        Route::put('products/{id}', function ($id) {
            // Handle product update
            return redirect()->route('admin.products.show', $id);
        })->name('products.update');
    });
    
    Route::middleware('ensure.permission:delete-products')->group(function () {
        Route::delete('products/{id}', function ($id) {
            // Handle product deletion
            return redirect()->route('admin.products.index');
        })->name('products.destroy');
    });
    
    // Categories
    Route::middleware('ensure.permission:view-categories')->group(function () {
        Route::get('categories', function () {
            return Inertia::render('admin/categories/Index');
        })->name('categories.index');
    });
    
    // Orders
    Route::middleware('ensure.permission:view-orders')->group(function () {
        Route::get('orders', function () {
            return Inertia::render('admin/orders/Index');
        })->name('orders.index');
        
        Route::get('orders/{id}', function ($id) {
            return Inertia::render('admin/orders/Show', ['id' => $id]);
        })->name('orders.show');
    });
    
    Route::middleware('ensure.permission:process-orders')->group(function () {
        Route::put('orders/{id}/status', function ($id) {
            // Handle order status update
            return redirect()->route('admin.orders.show', $id);
        })->name('orders.update-status');
    });
    
    // Users Management
    Route::middleware('ensure.permission:view-users')->group(function () {
        Route::get('users', function () {
            return Inertia::render('admin/users/Index');
        })->name('users.index');
        
        Route::get('users/{id}', function ($id) {
            return Inertia::render('admin/users/Show', ['id' => $id]);
        })->name('users.show');
    });
    
    Route::middleware('ensure.permission:edit-users')->group(function () {
        Route::get('users/{id}/edit', function ($id) {
            return Inertia::render('admin/users/Edit', ['id' => $id]);
        })->name('users.edit');
        
        Route::put('users/{id}', function ($id) {
            // Handle user update
            return redirect()->route('admin.users.show', $id);
        })->name('users.update');
    });
    
    // Reports - requires view-reports permission
    Route::middleware('ensure.permission:view-reports')->group(function () {
        Route::get('reports', function () {
            return Inertia::render('admin/reports/Index');
        })->name('reports.index');
        
        Route::get('reports/sales', function () {
            return Inertia::render('admin/reports/Sales');
        })->name('reports.sales');
        
        Route::get('reports/inventory', function () {
            return Inertia::render('admin/reports/Inventory');
        })->name('reports.inventory');
    });
    
    // System Settings - admin only
    Route::middleware('admin')->group(function () {
        Route::get('settings', function () {
            return Inertia::render('admin/settings/Index');
        })->name('settings.index');
        
        Route::get('settings/system', function () {
            return Inertia::render('admin/settings/System');
        })->name('settings.system');
        
        Route::put('settings/system', function () {
            // Handle system settings update
            return redirect()->route('admin.settings.system');
        })->name('settings.system.update');
    });
});
