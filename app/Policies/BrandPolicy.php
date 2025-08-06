<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BrandPolicy
{
    /**
     * Determine whether the user can view any brands.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('view brands');
    }

    /**
     * Determine whether the user can view the brand.
     */
    public function view(User $user, Brand $brand): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('view brands');
    }

    /**
     * Determine whether the user can create brands.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('create brands');
    }

    /**
     * Determine whether the user can update the brand.
     */
    public function update(User $user, Brand $brand): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('edit brands');
    }

    /**
     * Determine whether the user can delete the brand.
     */
    public function delete(User $user, Brand $brand): bool
    {
        // Check if brand has products
        if ($brand->products()->exists()) {
            return Response::deny('Cannot delete brand with associated products.');
        }

        return $user->hasRole('admin') || $user->hasPermissionTo('delete brands');
    }

    /**
     * Determine whether the user can restore the brand.
     */
    public function restore(User $user, Brand $brand): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('restore brands');
    }

    /**
     * Determine whether the user can permanently delete the brand.
     */
    public function forceDelete(User $user, Brand $brand): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can perform bulk operations.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('bulk edit brands');
    }

    /**
     * Determine whether the user can bulk delete brands.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('bulk delete brands');
    }
}
