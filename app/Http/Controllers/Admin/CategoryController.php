<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CategoryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Note: Removed authorizeResource to prevent JSON responses in Inertia
        // Manual authorization checks are added to each method instead
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Manual authorization check - this should work with admin middleware
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Admin access required.');
        }

        $query = Category::with(['parent', 'children'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->parent_id, function ($query, $parentId) {
                $query->where('parent_id', $parentId);
            });

        $categories = $query->ordered()->paginate(15)->withQueryString();

        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'parent_id']),
            'parentCategories' => Category::root()->active()->ordered()->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Manual authorization check
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Admin access required.');
        }

        return Inertia::render('admin/categories/Create', [
            'parentCategories' => Category::active()->ordered()->get(['id', 'name', 'parent_id']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $category = Category::create($request->validated());

            DB::commit();

            Log::info('Category created successfully', [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.categories.index')
                ->with('status', 'Category created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            throw CategoryException::databaseError(
                'Failed to create category: ' . $e->getMessage(),
                [
                    'request_data' => $request->validated(),
                    'user_id' => auth()->id(),
                    'original_error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): Response
    {
        // Manual authorization check
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Admin access required.');
        }

        $category->load([
            'parent',
            'children' => function ($query) {
                $query->ordered();
            },
            'products' => function ($query) {
                $query->with(['primaryImage', 'brand'])
                      ->orderBy('products.created_at', 'desc')
                      ->take(20); // Limit to 20 products for performance
            }
        ]);

        return Inertia::render('admin/categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): Response
    {
        // Manual authorization check
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Admin access required.');
        }

        return Inertia::render('admin/categories/Edit', [
            'category' => $category,
            'parentCategories' => Category::where('id', '!=', $category->id)
                ->active()
                ->ordered()
                ->get(['id', 'name', 'parent_id']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $originalData = $category->toArray();
            $category->update($request->validated());

            DB::commit();

            Log::info('Category updated successfully', [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'user_id' => auth()->id(),
                'changes' => $category->getChanges(),
                'original_data' => $originalData,
            ]);

            return redirect()->route('admin.categories.index')
                ->with('status', 'Category updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            throw CategoryException::databaseError(
                'Failed to update category: ' . $e->getMessage(),
                [
                    'category_id' => $category->id,
                    'request_data' => $request->validated(),
                    'user_id' => auth()->id(),
                    'original_error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        try {
            // Check if category has children
            if ($category->children()->exists()) {
                throw CategoryException::deletionConstraint(
                    'Cannot delete category with subcategories.',
                    [
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                        'children_count' => $category->children()->count(),
                    ]
                );
            }

            // Check if category has products
            if ($category->products()->exists()) {
                throw CategoryException::deletionConstraint(
                    'Cannot delete category with products.',
                    [
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                        'products_count' => $category->products()->count(),
                    ]
                );
            }

            DB::beginTransaction();

            $categoryData = $category->toArray();
            $category->delete();

            DB::commit();

            Log::info('Category deleted successfully', [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'user_id' => auth()->id(),
                'deleted_data' => $categoryData,
            ]);

            return redirect()->route('admin.categories.index')
                ->with('status', 'Category deleted successfully.');

        } catch (CategoryException $e) {
            // Re-throw CategoryException to maintain proper error handling
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            throw CategoryException::databaseError(
                'Failed to delete category: ' . $e->getMessage(),
                [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'user_id' => auth()->id(),
                    'original_error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Bulk update categories status.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        try {
            // Check authorization for bulk operations
            $this->authorize('bulkUpdate', Category::class);

            $request->validate([
                'category_ids' => ['required', 'array'],
                'category_ids.*' => ['exists:categories,id'],
                'is_active' => ['required', 'boolean'],
            ]);

            DB::beginTransaction();

            $updatedCount = Category::whereIn('id', $request->category_ids)
                ->update(['is_active' => $request->is_active]);

            DB::commit();

            $status = $request->is_active ? 'activated' : 'deactivated';

            Log::info('Bulk category status update completed', [
                'category_ids' => $request->category_ids,
                'is_active' => $request->is_active,
                'updated_count' => $updatedCount,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.categories.index')
                ->with('status', "{$updatedCount} categories {$status} successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            throw CategoryException::databaseError(
                'Failed to bulk update category status: ' . $e->getMessage(),
                [
                    'category_ids' => $request->category_ids ?? [],
                    'is_active' => $request->is_active ?? null,
                    'user_id' => auth()->id(),
                    'original_error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        // Check authorization for bulk delete operations
        $this->authorize('bulkDelete', Category::class);

        $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['exists:categories,id'],
        ]);

        // Check if any categories have children or products
        $categoriesWithChildren = Category::whereIn('id', $request->category_ids)
            ->whereHas('children')
            ->count();

        $categoriesWithProducts = Category::whereIn('id', $request->category_ids)
            ->whereHas('products')
            ->count();

        if ($categoriesWithChildren > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete categories that have subcategories.');
        }

        if ($categoriesWithProducts > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete categories that have products.');
        }

        $deletedCount = Category::whereIn('id', $request->category_ids)->delete();

        return redirect()->route('admin.categories.index')
            ->with('status', "{$deletedCount} categories deleted successfully.");
    }

    /**
     * Update category sort order.
     */
    public function updateSortOrder(Request $request): RedirectResponse
    {
        // Check authorization for sort order updates
        $this->authorize('updateSortOrder', Category::class);

        $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'exists:categories,id'],
            'categories.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->categories as $categoryData) {
            Category::where('id', $categoryData['id'])
                ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return redirect()->route('admin.categories.index')
            ->with('status', 'Category order updated successfully.');
    }
}
