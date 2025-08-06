import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

// Export cart types
export * from './cart';

export interface Auth {
    user: User;
    roles: string[];
    permissions: string[];
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Pagination Types
export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

// Form Types
export interface FormErrors {
    [key: string]: string[];
}

export interface SelectOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

// Filter Types
export interface ProductFilters {
    search?: string;
    status?: string;
    brand_id?: number;
    category_id?: number;
    price_min?: number;
    price_max?: number;
    is_featured?: boolean;
    is_on_sale?: boolean;
    stock_status?: string;
}

// Admin Types
export interface AdminStats {
    total_products: number;
    total_categories: number;
    total_brands: number;
    total_orders: number;
    total_revenue: number;
    low_stock_products: number;
}

// File Upload Types
export interface UploadedFile {
    id?: number;
    name: string;
    size: number;
    type: string;
    url?: string;
    preview?: string;
}

export interface ImageUploadResponse {
    success: boolean;
    message: string;
    data?: {
        id: number;
        path: string;
        url: string;
    };
}
