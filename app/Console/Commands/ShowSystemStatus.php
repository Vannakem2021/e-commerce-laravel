<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class ShowSystemStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show current system status and available data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 E-Commerce System Status');
        $this->line('');

        // Admin User Status
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $this->info('👤 Admin User: ✅ Ready');
            $this->line("   Email: {$adminUser->email}");
            $this->line("   Password: password123");
            $this->line("   Role: " . ($adminUser->hasRole('admin') ? 'admin' : 'none'));
            $this->line("   Permissions: " . $adminUser->getAllPermissions()->count());
        } else {
            $this->error('👤 Admin User: ❌ Not found');
        }

        $this->line('');

        // Categories Status
        $categoriesCount = Category::where('is_active', true)->count();
        $mainCategoriesCount = Category::whereNull('parent_id')->where('is_active', true)->count();
        $subcategoriesCount = Category::whereNotNull('parent_id')->where('is_active', true)->count();

        $this->info("🏷️  Categories: ✅ {$categoriesCount} total");
        $this->line("   Main Categories: {$mainCategoriesCount}");
        $this->line("   Subcategories: {$subcategoriesCount}");

        // Brands Status
        $brandsCount = Brand::where('is_active', true)->count();
        $featuredBrandsCount = Brand::where('is_active', true)->where('is_featured', true)->count();

        $this->info("🏢 Brands: ✅ {$brandsCount} total");
        $this->line("   Featured Brands: {$featuredBrandsCount}");

        // Products Status
        $productsCount = Product::where('status', 'published')->count();
        $featuredProductsCount = Product::where('status', 'published')->where('is_featured', true)->count();

        $this->info("📦 Products: ✅ {$productsCount} total");
        $this->line("   Featured Products: {$featuredProductsCount}");

        if ($productsCount > 0) {
            $this->line('');
            $this->line('📋 Sample Products:');
            $sampleProducts = Product::where('status', 'published')->take(3)->get();
            foreach ($sampleProducts as $product) {
                $this->line("   • {$product->name} - \${$product->formatted_price}");
            }
        }

        $this->line('');

        // Access Information
        $this->info('🌐 Access Information:');
        $this->line('   Admin Dashboard: /admin');
        $this->line('   Login Page: /login');
        $this->line('   Frontend: /');
        $this->line('   Cart: /cart');

        $this->line('');

        // Quick Actions
        $this->info('⚡ Quick Actions:');
        $this->line('   View Categories & Brands: php artisan list:categories-brands');
        $this->line('   Create Admin User: php artisan user:create-admin');
        $this->line('   Run Tests: php artisan test');

        $this->line('');
        $this->info('✅ System is ready for product creation and e-commerce operations!');

        return 0;
    }
}
