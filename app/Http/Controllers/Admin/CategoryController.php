<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
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
        return Inertia::render('admin/categories/Create', [
            'parentCategories' => Category::active()->ordered()->get(['id', 'name', 'parent_id']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('status', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): Response
    {
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
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('status', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Check if category has children or products
        if ($category->children()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with subcategories.');
        }

        if ($category->products()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with products.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('status', 'Category deleted successfully.');
    }

    /**
     * Bulk update categories status.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['exists:categories,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $updatedCount = Category::whereIn('id', $request->category_ids)
            ->update(['is_active' => $request->is_active]);

        $status = $request->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.categories.index')
            ->with('status', "{$updatedCount} categories {$status} successfully.");
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
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
