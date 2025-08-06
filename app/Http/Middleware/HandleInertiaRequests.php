<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Services\CartService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        // Temporarily disable version checking to fix 409 errors
        return null;
    }

    /**
     * Define the props that are shared by default.
     *
     * Shares authentication data including user info, roles, and permissions
     * with the Vue frontend for role-based UI rendering.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user()?->getRoleNames(),
                'permissions' => $request->user()?->getPermissionNames(),
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'categories' => $this->getSharedCategories(),
            'featured_categories' => $this->getFeaturedCategories(),
            'cart_summary' => $this->getCartSummary(),
        ];
    }

    /**
     * Get shared categories data for navigation and components.
     */
    private function getSharedCategories(): array
    {
        return Category::with(['children' => function ($query) {
                $query->where('is_active', true)
                      ->withCount(['products as product_count' => function ($q) {
                          $q->where('status', 'published');
                      }])
                      ->orderBy('sort_order')
                      ->orderBy('name');
            }])
            ->withCount(['products as product_count' => function ($query) {
                $query->where('status', 'published');
            }])
            ->where('is_active', true)
            ->whereNull('parent_id') // Only root categories
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'image', 'icon', 'sort_order'])
            ->map(function ($category) {
                // Calculate total product count including subcategories
                $subcategoryProductCount = 0;
                if ($category->children) {
                    foreach ($category->children as $child) {
                        $subcategoryProductCount += $child->product_count;
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
                    'product_count' => $category->product_count + $subcategoryProductCount,
                    'children' => $category->children ? $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'description' => $child->description,
                            'product_count' => $child->product_count,
                        ];
                    }) : [],
                ];
            })
            ->toArray();
    }

    /**
     * Get featured categories for homepage display.
     */
    private function getFeaturedCategories(): array
    {
        // Get featured subcategories (these are the most specific and useful for display)
        $featuredCategories = Category::where('is_active', true)
            ->where('is_featured', true)
            ->whereNotNull('parent_id') // Only subcategories
            ->withCount(['products as product_count' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'image', 'icon', 'sort_order']);

        // Format for frontend
        return $featuredCategories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description ?: "Latest {$category->name} products",
                'product_count' => $category->product_count,
                'href' => "/products?category={$category->slug}",
                'icon' => $this->getCategoryIcon($category->slug),
                'color' => $this->getCategoryColor($category->slug),
            ];
        })->toArray();
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
     * Get cart summary for shared data.
     */
    private function getCartSummary(): array
    {
        try {
            $cartService = app(CartService::class);
            return $cartService->getCartSummary();
        } catch (\Exception $e) {
            // Return empty cart summary if there's an error
            return [
                'id' => 0,
                'total_quantity' => 0,
                'total_price' => 0,
                'formatted_total' => '$0.00',
                'items_count' => 0,
                'is_empty' => true,
            ];
        }
    }
}

