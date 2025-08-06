<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only admins can view category management interface
        try {
            return $user->hasRole('admin');
        } catch (\Exception $e) {
            // Log the error and deny access
            \Log::error('Error checking admin role in CategoryPolicy::viewAny', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $category): bool
    {
        // Only admins can view individual categories in admin interface
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins can create categories
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category): bool
    {
        // Only admins can update categories
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category): Response|bool
    {
        // Only admins can delete categories
        // Additional business logic: cannot delete categories with children or products
        if (!$user->hasRole('admin')) {
            return false;
        }

        // Check if category has children
        if ($category->children()->exists()) {
            return Response::deny('Cannot delete category with subcategories.');
        }

        // Check if category has products
        if ($category->products()->exists()) {
            return Response::deny('Cannot delete category with products.');
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $category): bool
    {
        // Only admins can restore soft-deleted categories
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        // Only admins can permanently delete categories
        // This should be very restricted - perhaps only super admins
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can perform bulk operations.
     */
    public function bulkUpdate(User $user): bool
    {
        // Only admins can perform bulk status updates
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can perform bulk delete operations.
     */
    public function bulkDelete(User $user): bool
    {
        // Only admins can perform bulk delete operations
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update category sort order.
     */
    public function updateSortOrder(User $user): bool
    {
        // Only admins can update category sort order
        return $user->hasRole('admin');
    }
}
