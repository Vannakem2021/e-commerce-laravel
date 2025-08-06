<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | This file contains feature flags for gradual rollout of new features
    | and deprecation of old ones. These flags allow safe migration between
    | different architectural approaches.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Inertia.js Migration Features
    |--------------------------------------------------------------------------
    |
    | These flags control the migration from mixed API/Inertia architecture
    | to a fully consistent Inertia.js architecture.
    |
    */

    // Phase 1: Category data migration
    'use_inertia_categories' => env('FEATURE_INERTIA_CATEGORIES', true),
    'deprecate_category_apis' => env('FEATURE_DEPRECATE_CATEGORY_APIS', false),

    // Phase 2: Cart initialization migration
    'use_inertia_cart_init' => env('FEATURE_INERTIA_CART_INIT', false),

    // Phase 3: Enhanced navigation
    'use_enhanced_inertia_navigation' => env('FEATURE_ENHANCED_INERTIA_NAV', false),

    /*
    |--------------------------------------------------------------------------
    | Performance Features
    |--------------------------------------------------------------------------
    |
    | Features related to performance optimization and caching.
    |
    */

    // Category caching
    'cache_shared_categories' => env('FEATURE_CACHE_SHARED_CATEGORIES', true),
    'category_cache_ttl' => env('CATEGORY_CACHE_TTL', 3600), // 1 hour

    // Featured categories caching
    'cache_featured_categories' => env('FEATURE_CACHE_FEATURED_CATEGORIES', true),
    'featured_category_cache_ttl' => env('FEATURED_CATEGORY_CACHE_TTL', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Development Features
    |--------------------------------------------------------------------------
    |
    | Features for development and debugging purposes.
    |
    */

    // API deprecation warnings
    'log_deprecated_api_usage' => env('FEATURE_LOG_DEPRECATED_API', true),
    'show_deprecation_headers' => env('FEATURE_DEPRECATION_HEADERS', false),

    // Debug shared data
    'debug_shared_data' => env('FEATURE_DEBUG_SHARED_DATA', false),
];
