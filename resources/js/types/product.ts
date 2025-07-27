export interface FeaturedProduct {
    id: number;
    name: string;
    price: number;
    originalPrice?: number;
    image: string;
    category: string;
    rating?: number;
    isOnSale?: boolean;
    href: string;
}
