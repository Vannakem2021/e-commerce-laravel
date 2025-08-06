# Brand Page Troubleshooting Guide

## Issue: Brand page showing JSON instead of rendered page

### Immediate Steps to Fix:

1. **Clear all caches:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

2. **Rebuild frontend assets:**
```bash
npm run build
# or for development
npm run dev
```

3. **Check if routes are registered correctly:**
```bash
php artisan route:list | grep brand
```

4. **Verify the Brand model and controller:**
```bash
php artisan tinker
```
Then in tinker:
```php
// Test if Brand model works
App\Models\Brand::count()

// Test if BrandService can be resolved
app(App\Services\BrandService::class)

// Test the controller method directly
$controller = new App\Http\Controllers\BrandController(app(App\Services\BrandService::class));
```

### Potential Issues and Solutions:

#### 1. **Service Provider Not Registered**
Make sure the BrandService is properly registered in `AppServiceProvider.php`:
```php
// In register() method
$this->app->singleton(BrandService::class, function ($app) {
    return new BrandService();
});
```

#### 2. **Missing Component Import**
The `BrandProducts.vue` component might have missing imports. Check:
- All UI components are imported correctly
- All icons from lucide-vue-next exist
- ProductCard and ProductFilters components exist

#### 3. **Route Conflict**
There might be a route conflict. Check if there are duplicate routes in `web.php`.

#### 4. **Inertia Response Issue**
The controller might not be returning a proper Inertia response. Verify:
```php
// In BrandController@index
return Inertia::render('Brands', [
    'brands' => $brands,
]);
```

#### 5. **Frontend Build Issue**
The Vue component might not be compiled correctly:
```bash
# Check if the component exists
ls -la resources/js/pages/Brands.vue
ls -la resources/js/pages/BrandProducts.vue

# Rebuild with verbose output
npm run build -- --verbose
```

### Debug Steps:

1. **Check Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

2. **Check browser console for JavaScript errors**

3. **Test with a simple controller:**
```php
// Temporarily replace BrandController@index with:
public function index(): Response
{
    return Inertia::render('Brands', [
        'brands' => collect([
            ['id' => 1, 'name' => 'Test Brand', 'slug' => 'test-brand', 'is_featured' => false]
        ]),
    ]);
}
```

4. **Verify Inertia is working:**
Visit any other page that uses Inertia (like `/products`) to confirm Inertia is working correctly.

### Quick Fix Commands:

```bash
# 1. Clear everything
php artisan optimize:clear

# 2. Rebuild autoloader
composer dump-autoload

# 3. Rebuild frontend
npm run build

# 4. Restart server (if using artisan serve)
php artisan serve
```

### If Still Not Working:

1. **Temporarily revert to the old route:**
```php
// In routes/web.php, replace the brand routes with:
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
```

2. **Check if the issue is with the BrandService:**
Remove the BrandService dependency temporarily and use direct queries.

3. **Verify the Brand model:**
```php
// In tinker
$brand = App\Models\Brand::first();
$brand->products_count; // Should work if withCount is applied
```

### Expected Behavior:
- `/brands` should show the brand listing page
- `/brands/{slug}` should show individual brand products
- Both should render Vue components, not JSON

### Common Errors:
- "Class BrandService not found" → Service not registered
- "Component not found" → Frontend build issue
- "Route not found" → Route cache issue
- JSON response → Controller or Inertia issue
