import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable for managing category data and operations.
 * Provides standardized access to category data from Inertia shared props.
 */
export function useCategories() {
    const page = usePage();
    const isLoading = ref(false);
    const error = ref(null);

    /**
     * Get raw categories from Inertia shared props.
     */
    const rawCategories = computed(() => {
        try {
            const cats = page.props.categories || [];

            // Validate data structure
            if (!Array.isArray(cats)) {
                console.warn('Categories data is not an array:', cats);
                return [];
            }

            return cats;
        } catch (err) {
            console.error('Error accessing categories from props:', err);
            error.value = 'Failed to load categories';
            return [];
        }
    });

    /**
     * Get featured categories from Inertia shared props.
     */
    const featuredCategories = computed(() => {
        try {
            const featured = page.props.featuredCategories || [];

            // Validate data structure
            if (!Array.isArray(featured)) {
                console.warn('Featured categories data is not an array:', featured);
                return [];
            }

            return featured;
        } catch (err) {
            console.error('Error accessing featured categories from props:', err);
            error.value = 'Failed to load featured categories';
            return [];
        }
    });

    /**
     * Transform categories for dropdown navigation.
     */
    const categoryDropdowns = computed(() => {
        try {
            if (!rawCategories.value.length) {
                return [];
            }

            return rawCategories.value.map(category => {
                // Validate required category fields
                if (!category.id || !category.name || !category.slug) {
                    console.warn('Invalid category structure:', category);
                    return null;
                }

                return {
                    id: category.id,
                    name: category.name,
                    href: `/products?category=${category.slug}`,
                    description: category.description || `Browse ${category.name} products`,
                    productCount: category.product_count || 0,
                    icon: category.icon || 'TagIcon',
                    children: (category.children || []).map(child => {
                        if (!child.id || !child.name || !child.slug) {
                            console.warn('Invalid child category structure:', child);
                            return null;
                        }

                        return {
                            id: child.id,
                            name: child.name,
                            href: `/products?category=${child.slug}`,
                            description: child.description || `Browse ${child.name} products`,
                            productCount: child.product_count || 0,
                        };
                    }).filter(Boolean), // Remove null entries
                };
            }).filter(Boolean); // Remove null entries
        } catch (err) {
            console.error('Error transforming categories for dropdowns:', err);
            error.value = 'Failed to process categories';
            return [];
        }
    });

    /**
     * Get category by slug.
     */
    const getCategoryBySlug = (slug) => {
        if (!slug || !rawCategories.value.length) {
            return null;
        }

        // Search in root categories
        for (const category of rawCategories.value) {
            if (category.slug === slug) {
                return category;
            }

            // Search in children
            if (category.children) {
                for (const child of category.children) {
                    if (child.slug === slug) {
                        return child;
                    }
                }
            }
        }

        return null;
    };

    /**
     * Get category breadcrumbs for a given category slug.
     */
    const getCategoryBreadcrumbs = (slug) => {
        const category = getCategoryBySlug(slug);
        if (!category) {
            return [];
        }

        const breadcrumbs = [];

        // If it's a child category, find its parent
        if (category.parent_id) {
            const parent = rawCategories.value.find(cat => cat.id === category.parent_id);
            if (parent) {
                breadcrumbs.push({
                    name: parent.name,
                    href: `/products?category=${parent.slug}`,
                });
            }
        }

        // Add current category
        breadcrumbs.push({
            name: category.name,
            href: `/products?category=${category.slug}`,
        });

        return breadcrumbs;
    };

    /**
     * Check if categories data is available.
     */
    const hasCategories = computed(() => {
        return rawCategories.value.length > 0;
    });

    /**
     * Check if featured categories data is available.
     */
    const hasFeaturedCategories = computed(() => {
        return featuredCategories.value.length > 0;
    });

    /**
     * Get loading state for UI components.
     */
    const isLoadingCategories = computed(() => {
        return isLoading.value;
    });

    /**
     * Get error state for UI components.
     */
    const categoryError = computed(() => {
        return error.value;
    });

    /**
     * Clear any existing errors.
     */
    const clearError = () => {
        error.value = null;
    };

    /**
     * Refresh categories (placeholder for future API integration).
     */
    const refreshCategories = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            // Categories come from Inertia shared props on page load
            // For a small/medium app, page refresh is sufficient
            window.location.reload();
        } catch (err) {
            error.value = 'Failed to refresh categories';
            console.error('Error refreshing categories:', err);
        } finally {
            isLoading.value = false;
        }
    };

    return {
        // Data
        categories: rawCategories,
        featuredCategories,
        categoryDropdowns,

        // State
        isLoadingCategories,
        categoryError,
        hasCategories,
        hasFeaturedCategories,

        // Methods
        getCategoryBySlug,
        getCategoryBreadcrumbs,
        clearError,
        refreshCategories,
    };
}
