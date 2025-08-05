// Core Product Types
export interface Product {
    id: number;
    name: string;
    slug: string;
    sku: string;
    short_description?: string;
    description?: string;
    features?: string;
    specifications?: string;
    price: number;
    compare_price?: number;
    cost_price?: number;
    stock_quantity: number;
    stock_status: 'in_stock' | 'out_of_stock' | 'back_order';
    low_stock_threshold?: number;
    track_inventory: boolean;
    product_type: 'simple' | 'variable' | 'digital' | 'service';
    is_digital: boolean;
    is_virtual: boolean;
    requires_shipping: boolean;
    status: 'draft' | 'published' | 'archived';
    is_featured: boolean;
    is_on_sale: boolean;
    published_at?: string;
    meta_title?: string;
    meta_description?: string;
    seo_data?: Record<string, any>;
    brand_id?: number;
    user_id: number;
    weight?: number;
    length?: number;
    width?: number;
    height?: number;
    sort_order: number;
    additional_data?: Record<string, any>;
    created_at: string;
    updated_at: string;
    deleted_at?: string;

    // Relationships
    brand?: Brand;
    categories?: Category[];
    images?: ProductImage[];
    variants?: ProductVariant[];
    activeVariants?: ProductVariant[];
    tags?: ProductTag[];
    primaryImage?: ProductImage;
}

export interface Brand {
    id: number;
    name: string;
    slug: string;
    description?: string;
    logo?: string;
    website?: string;
    is_active: boolean;
    sort_order: number;
    meta_title?: string;
    meta_description?: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string;

    // Relationships
    products?: Product[];
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    parent_id?: number;
    is_active: boolean;
    sort_order: number;
    meta_title?: string;
    meta_description?: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string;

    // Relationships
    parent?: Category;
    children?: Category[];
    products?: Product[];
}

export interface ProductImage {
    id: number;
    product_id: number;
    image_path: string;
    alt_text?: string;
    is_primary: boolean;
    sort_order: number;
    created_at: string;
    updated_at: string;

    // Relationships
    product?: Product;
}

export interface ProductVariant {
    id: number;
    product_id: number;
    sku: string;
    name?: string;
    price?: number; // In cents, nullable (inherits from product if null)
    compare_price?: number; // In cents
    cost_price?: number; // In cents
    stock_quantity: number;
    stock_status: 'in_stock' | 'out_of_stock' | 'back_order';
    low_stock_threshold: number;
    weight?: number;
    length?: number;
    width?: number;
    height?: number;
    is_active: boolean;
    sort_order: number;
    image?: string; // Variant-specific image path
    created_at: string;
    updated_at: string;

    // Computed properties
    effective_price?: number;
    display_name?: string;
    image_url?: string;
    price_in_dollars?: number;
    effective_price_in_dollars?: number;

    // Relationships
    product?: Product;
}

export interface ProductTag {
    id: number;
    name: string;
    slug: string;
    description?: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;

    // Relationships
    products?: Product[];
}

// Legacy interfaces for backward compatibility
export interface FeaturedProduct {
    id: number;
    name: string;
    price: number;
    originalPrice?: number;
    image: string;
    category: string;
    isOnSale?: boolean;
    href: string;
    description?: string;
}

export interface ProductDetail {
    id: number;
    name: string;
    price: number;
    originalPrice?: number;
    images: string[];
    category: string;
    isOnSale?: boolean;
    description: string;
    features: string[];
    specifications: Record<string, string>;
    inStock: boolean;
    stockCount?: number;
    brand: string;
    sku: string;
}
