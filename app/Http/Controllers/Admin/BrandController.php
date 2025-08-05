<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brand\StoreBrandRequest;
use App\Http\Requests\Admin\Brand\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Brand::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            });

        $brands = $query->ordered()->paginate(15)->withQueryString();

        return Inertia::render('admin/brands/Index', [
            'brands' => $brands,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/brands/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrandRequest $request): RedirectResponse
    {
        Brand::create($request->validated());

        return redirect()->route('admin.brands.index')
            ->with('status', 'Brand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand): Response
    {
        $brand->load(['products' => function ($query) {
            $query->published()->with('primaryImage')->latest()->take(10);
        }]);

        return Inertia::render('admin/brands/Show', [
            'brand' => $brand,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand): Response
    {
        return Inertia::render('admin/brands/Edit', [
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validated());

        return redirect()->route('admin.brands.index')
            ->with('status', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        // Check if brand has products
        if ($brand->products()->exists()) {
            return redirect()->route('admin.brands.index')
                ->with('error', 'Cannot delete brand with products.');
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('status', 'Brand deleted successfully.');
    }

    /**
     * Bulk update brands status.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'brand_ids' => ['required', 'array'],
            'brand_ids.*' => ['exists:brands,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $updatedCount = Brand::whereIn('id', $request->brand_ids)
            ->update(['is_active' => $request->is_active]);

        $status = $request->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.brands.index')
            ->with('status', "{$updatedCount} brands {$status} successfully.");
    }

    /**
     * Bulk delete brands.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'brand_ids' => ['required', 'array'],
            'brand_ids.*' => ['exists:brands,id'],
        ]);

        // Check if any brands have products
        $brandsWithProducts = Brand::whereIn('id', $request->brand_ids)
            ->whereHas('products')
            ->count();

        if ($brandsWithProducts > 0) {
            return redirect()->route('admin.brands.index')
                ->with('error', 'Cannot delete brands that have products assigned.');
        }

        $deletedCount = Brand::whereIn('id', $request->brand_ids)->delete();

        return redirect()->route('admin.brands.index')
            ->with('status', "{$deletedCount} brands deleted successfully.");
    }

    /**
     * Update brand sort order.
     */
    public function updateSortOrder(Request $request): RedirectResponse
    {
        $request->validate([
            'brands' => ['required', 'array'],
            'brands.*.id' => ['required', 'exists:brands,id'],
            'brands.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->brands as $brandData) {
            Brand::where('id', $brandData['id'])
                ->update(['sort_order' => $brandData['sort_order']]);
        }

        return redirect()->route('admin.brands.index')
            ->with('status', 'Brand order updated successfully.');
    }
}
