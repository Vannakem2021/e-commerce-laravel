<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Services\BrandService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    public function __construct(
        private BrandService $brandService
    ) {}

    /**
     * Display a listing of all brands.
     */
    public function index(): Response
    {
        // Temporarily use direct query to debug
        $brands = Brand::where('is_active', true)
            ->withCount(['products' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Brands', [
            'brands' => $brands,
        ]);
    }

    /**
     * Display the specified brand and its products.
     */
    public function show(Request $request, string $slug): Response
    {
        $brand = Brand::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Build product query with filters
        $query = Product::with(['primaryImage', 'categories'])
            ->where('brand_id', $brand->id)
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
            ->when($request->min_price, function ($query, $minPrice) {
                $query->where('price', '>=', $minPrice * 100); // Convert to cents
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                $query->where('price', '<=', $maxPrice * 100); // Convert to cents
            })
            ->when($request->featured, function ($query) {
                $query->where('is_featured', true);
            })
            ->when($request->on_sale, function ($query) {
                $query->where('is_on_sale', true);
            });

        // Apply sorting
        switch ($request->sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->orderBy('sort_order')->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();

        // Get categories for filtering (only categories that have products from this brand)
        $categories = $brand->products()
            ->where('status', 'published')
            ->with('categories')
            ->get()
            ->pluck('categories')
            ->flatten()
            ->unique('id')
            ->where('is_active', true)
            ->sortBy('name')
            ->values();

        // Get price range for this brand
        $priceRange = Product::where('brand_id', $brand->id)
            ->where('status', 'published')
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return Inertia::render('BrandProducts', [
            'brand' => $brand,
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'min_price', 'max_price', 'featured', 'on_sale', 'sort']),
            'priceRange' => [
                'min' => $priceRange ? floor($priceRange->min_price / 100) : 0,
                'max' => $priceRange ? ceil($priceRange->max_price / 100) : 1000,
            ],
            'meta' => [
                'title' => $brand->meta_title ?: "{$brand->name} Products - " . config('app.name'),
                'description' => $brand->meta_description ?: "Shop {$brand->name} products. {$brand->description}",
            ],
        ]);
    }

    /**
     * Get brand suggestions for search autocomplete.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $brands = Brand::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->withCount(['products' => function ($query) {
                $query->where('status', 'published');
            }])
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->take(5)
            ->get(['id', 'name', 'slug']);

        return response()->json($brands);
    }
}
