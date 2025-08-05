<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
// Removed ProductAttribute import
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of products for public viewing.
     */
    public function index(Request $request): Response
    {
        $query = Product::with(['brand', 'categories', 'primaryImage', 'images'])
            ->where('status', 'published')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%");
            })
            ->when($request->category, function ($query, $category) {
                $query->whereHas('categories', function ($q) use ($category) {
                    $q->where('categories.slug', $category);
                });
            })
            ->when($request->brand, function ($query, $brand) {
                $query->whereHas('brand', function ($q) use ($brand) {
                    $q->where('brands.slug', $brand);
                });
            })
            ->when($request->min_price, function ($query, $minPrice) {
                $query->where('price', '>=', $minPrice * 100); // Convert to cents for comparison
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                $query->where('price', '<=', $maxPrice * 100); // Convert to cents for comparison
            })
            ->when($request->featured, function ($query) {
                $query->where('is_featured', true);
            })
            ->when($request->on_sale, function ($query) {
                $query->where('is_on_sale', true);
            })
            ->when($request->sort, function ($query, $sort) {
                switch ($sort) {
                    case 'price_low':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price_high':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'name':
                        $query->orderBy('name', 'asc');
                        break;
                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    default:
                        $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
                }
            }, function ($query) {
                // Default sorting
                $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
            });

        $products = $query->paginate(12)->withQueryString();

        // Get filter options with product counts
        $brands = Brand::where('is_active', true)
            ->withCount(['products' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                    'product_count' => $brand->products_count,
                ];
            });

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id') // Only root categories for main filter
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        // Get price range
        $priceRange = Product::where('status', 'published')
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return Inertia::render('Products', [
            'products' => $products,
            'filters' => $request->only(['search', 'category', 'brand', 'min_price', 'max_price', 'featured', 'on_sale', 'sort']),
            'brands' => $brands,
            'categories' => $categories,
            'priceRange' => [
                'min' => $priceRange ? floor($priceRange->min_price / 100) : 0,
                'max' => $priceRange ? ceil($priceRange->max_price / 100) : 1000,
            ],
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(string $slug): Response
    {
        $product = Product::with([
            'brand',
            'categories',
            'images' => function ($query) {
                $query->orderBy('sort_order');
            },
            'primaryImage',
            'variants' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            },
            // Removed attribute relationships
        ])
        ->where('slug', $slug)
        ->where('status', 'published')
        ->firstOrFail();

        // Get related products (same category, different product)
        $relatedProducts = Product::with(['brand', 'primaryImage'])
            ->where('status', 'published')
            ->where('id', '!=', $product->id)
            ->whereHas('categories', function ($query) use ($product) {
                $query->whereIn('categories.id', $product->categories->pluck('id'));
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        return Inertia::render('ProductDetail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    // Removed attribute-related methods

    /**
     * Search products via AJAX.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::with(['brand', 'primaryImage'])
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'name', 'slug', 'price', 'sku']);

        return response()->json($products);
    }

    /**
     * Get featured products for homepage.
     */
    public function featured()
    {
        $products = Product::with(['brand', 'primaryImage'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        return response()->json($products);
    }

    /**
     * Get products on sale.
     */
    public function onSale()
    {
        $products = Product::with(['brand', 'primaryImage'])
            ->where('status', 'published')
            ->where('is_on_sale', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        return response()->json($products);
    }

    /**
     * Get new arrivals.
     */
    public function newArrivals()
    {
        $products = Product::with(['brand', 'primaryImage'])
            ->where('status', 'published')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return response()->json($products);
    }

    // Removed attribute-related API methods

    /**
     * Get categories for frontend components.
     */
    public function getCategories(): JsonResponse
    {
        $categories = Category::with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
            }])
            ->where('is_active', true)
            ->whereNull('parent_id') // Only root categories
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'image', 'icon', 'sort_order']);

        // Add product counts for each category
        $categoriesWithCounts = $categories->map(function ($category) {
            $productCount = $category->products()->where('status', 'published')->count();

            // Also count products in subcategories
            $subcategoryProductCount = 0;
            if ($category->children) {
                foreach ($category->children as $child) {
                    $subcategoryProductCount += $child->products()->where('status', 'published')->count();
                }
            }

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'icon' => $category->icon,
                'sort_order' => $category->sort_order,
                'product_count' => $productCount + $subcategoryProductCount,
                'children' => $category->children ? $category->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'description' => $child->description,
                        'product_count' => $child->products()->where('status', 'published')->count(),
                    ];
                }) : [],
            ];
        });

        return response()->json([
            'categories' => $categoriesWithCounts
        ]);
    }

    /**
     * Get featured categories for homepage display.
     */
    public function getFeaturedCategories(): JsonResponse
    {
        // Get featured subcategories (these are the most specific and useful for display)
        $featuredCategories = Category::where('is_active', true)
            ->where('is_featured', true)
            ->whereNotNull('parent_id') // Only subcategories
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'image', 'icon', 'sort_order']);

        // Add product counts and format for frontend
        $categoriesWithCounts = $featuredCategories->map(function ($category) {
            $productCount = $category->products()->where('status', 'published')->count();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description ?: "Latest {$category->name} products",
                'product_count' => $productCount,
                'href' => "/products?category={$category->slug}",
                'icon' => $this->getCategoryIcon($category->slug),
                'color' => $this->getCategoryColor($category->slug),
            ];
        });

        return response()->json([
            'categories' => $categoriesWithCounts
        ]);
    }

    /**
     * Get icon name for category based on slug.
     */
    private function getCategoryIcon(string $slug): string
    {
        $iconMap = [
            'smartphones' => 'smartphone',
            'iphone' => 'smartphone',
            'samsung-galaxy' => 'smartphone',
            'tablets' => 'tablet',
            'ipad' => 'tablet',
            'laptops' => 'laptop',
            'gaming-laptops' => 'gamepad-2',
            'business-laptops' => 'briefcase',
            'desktop-computers' => 'monitor',
            'headphones' => 'headphones',
            'wireless-headphones' => 'bluetooth',
            'gaming-headsets' => 'gamepad-2',
            'speakers' => 'speaker',
        ];

        return $iconMap[$slug] ?? 'package';
    }

    /**
     * Get color class for category based on slug.
     */
    private function getCategoryColor(string $slug): string
    {
        $colorMap = [
            'smartphones' => 'from-blue-500 to-blue-600',
            'iphone' => 'from-gray-500 to-gray-600',
            'samsung-galaxy' => 'from-blue-500 to-blue-600',
            'tablets' => 'from-purple-500 to-purple-600',
            'ipad' => 'from-gray-500 to-gray-600',
            'laptops' => 'from-green-500 to-green-600',
            'gaming-laptops' => 'from-red-500 to-red-600',
            'business-laptops' => 'from-indigo-500 to-indigo-600',
            'desktop-computers' => 'from-slate-500 to-slate-600',
            'headphones' => 'from-orange-500 to-orange-600',
            'wireless-headphones' => 'from-cyan-500 to-cyan-600',
            'gaming-headsets' => 'from-red-500 to-red-600',
            'speakers' => 'from-yellow-500 to-yellow-600',
        ];

        return $colorMap[$slug] ?? 'from-gray-500 to-gray-600';
    }

    /**
     * Display all categories page.
     */
    public function categories(): Response
    {
        $categories = Category::with(['children' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
            }])
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Display products for a specific category.
     */
    public function categoryProducts(Category $category): Response
    {
        // Get products for this category and its children
        $categoryIds = [$category->id];
        if ($category->children) {
            $categoryIds = array_merge($categoryIds, $category->children->pluck('id')->toArray());
        }

        $products = Product::with(['images', 'brand', 'categories'])
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return Inertia::render('Categories/Show', [
            'category' => $category->load('children'),
            'products' => $products,
            'breadcrumbs' => [
                ['name' => 'Home', 'href' => '/'],
                ['name' => 'Categories', 'href' => '/categories'],
                ['name' => $category->name, 'href' => null],
            ],
        ]);
    }
}
