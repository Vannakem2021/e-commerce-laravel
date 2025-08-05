<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Console\Command;

class ListCategoriesAndBrands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:categories-brands';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available categories and brands for product creation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📦 Available Categories and Brands for Product Creation');
        $this->line('');

        // Display Categories
        $this->info('🏷️  CATEGORIES:');
        $this->line('');

        $mainCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($mainCategories as $category) {
            $this->line("📁 {$category->name} (ID: {$category->id})");
            $this->line("   └─ Slug: {$category->slug}");
            
            $subcategories = Category::where('parent_id', $category->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            foreach ($subcategories as $subcategory) {
                $this->line("   ├─ {$subcategory->name} (ID: {$subcategory->id})");
                $this->line("   │  └─ Slug: {$subcategory->slug}");
            }
            $this->line('');
        }

        // Display Brands
        $this->info('🏢 BRANDS:');
        $this->line('');

        $brands = Brand::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $featuredBrands = $brands->where('is_featured', true);
        $otherBrands = $brands->where('is_featured', false);

        $this->line('⭐ Featured Brands:');
        foreach ($featuredBrands as $brand) {
            $this->line("   • {$brand->name} (ID: {$brand->id}) - {$brand->slug}");
        }

        $this->line('');
        $this->line('📋 Other Brands:');
        foreach ($otherBrands as $brand) {
            $this->line("   • {$brand->name} (ID: {$brand->id}) - {$brand->slug}");
        }

        $this->line('');
        $this->info('📊 Summary:');
        $this->line("   Total Categories: " . Category::where('is_active', true)->count());
        $this->line("   Main Categories: " . $mainCategories->count());
        $this->line("   Subcategories: " . Category::whereNotNull('parent_id')->where('is_active', true)->count());
        $this->line("   Total Brands: " . $brands->count());
        $this->line("   Featured Brands: " . $featuredBrands->count());

        $this->line('');
        $this->info('✅ You can now easily create products using these categories and brands!');
        $this->line('💡 Tip: Use the admin dashboard at /admin to create new products.');

        return 0;
    }
}
