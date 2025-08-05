/**
 * Price utility functions for consistent price handling across the application.
 * 
 * The application stores prices in cents (integers) for precision and consistency.
 * These utilities help convert between cents and dollars for display and input.
 */

/**
 * Convert cents to dollars for display
 * @param cents - Price in cents (integer)
 * @returns Price in dollars (float)
 */
export function centsToDollars(cents: number | null | undefined): number {
    if (cents === null || cents === undefined) return 0;
    return cents / 100;
}

/**
 * Convert dollars to cents for storage
 * @param dollars - Price in dollars (float)
 * @returns Price in cents (integer)
 */
export function dollarsToCents(dollars: number | null | undefined): number {
    if (dollars === null || dollars === undefined) return 0;
    return Math.round(dollars * 100);
}

/**
 * Format price in cents as currency string
 * @param cents - Price in cents (integer)
 * @param currency - Currency symbol (default: '$')
 * @returns Formatted price string (e.g., '$10.99')
 */
export function formatPrice(cents: number | null | undefined, currency: string = '$'): string {
    if (cents === null || cents === undefined) return `${currency}0.00`;
    const dollars = centsToDollars(cents);
    return `${currency}${dollars.toFixed(2)}`;
}

/**
 * Format price range for display
 * @param minCents - Minimum price in cents
 * @param maxCents - Maximum price in cents
 * @param currency - Currency symbol (default: '$')
 * @returns Formatted price range string (e.g., '$10.99 - $19.99' or '$10.99')
 */
export function formatPriceRange(
    minCents: number | null | undefined, 
    maxCents: number | null | undefined, 
    currency: string = '$'
): string {
    if (!minCents && !maxCents) return `${currency}0.00`;
    if (!maxCents || minCents === maxCents) return formatPrice(minCents, currency);
    return `${formatPrice(minCents, currency)} - ${formatPrice(maxCents, currency)}`;
}

/**
 * Calculate discount percentage
 * @param originalCents - Original price in cents
 * @param saleCents - Sale price in cents
 * @returns Discount percentage (0-100)
 */
export function calculateDiscountPercentage(
    originalCents: number | null | undefined, 
    saleCents: number | null | undefined
): number {
    if (!originalCents || !saleCents || originalCents <= saleCents) return 0;
    return Math.round(((originalCents - saleCents) / originalCents) * 100);
}

/**
 * Calculate savings amount
 * @param originalCents - Original price in cents
 * @param saleCents - Sale price in cents
 * @returns Savings amount in cents
 */
export function calculateSavings(
    originalCents: number | null | undefined, 
    saleCents: number | null | undefined
): number {
    if (!originalCents || !saleCents || originalCents <= saleCents) return 0;
    return originalCents - saleCents;
}

/**
 * Check if product is on sale
 * @param price - Current price in cents
 * @param comparePrice - Compare price in cents
 * @returns True if product is on sale
 */
export function isOnSale(
    price: number | null | undefined, 
    comparePrice: number | null | undefined
): boolean {
    return !!(comparePrice && price && comparePrice > price);
}

/**
 * Get the effective price (lowest price) for display
 * @param price - Current price in cents
 * @param comparePrice - Compare price in cents
 * @returns Effective price in cents
 */
export function getEffectivePrice(
    price: number | null | undefined, 
    comparePrice: number | null | undefined
): number {
    if (!price) return 0;
    if (!comparePrice) return price;
    return Math.min(price, comparePrice);
}

/**
 * Validate price input (for forms)
 * @param value - Price value to validate
 * @returns True if valid price
 */
export function isValidPrice(value: any): boolean {
    if (value === null || value === undefined || value === '') return false;
    const num = parseFloat(value);
    return !isNaN(num) && num >= 0 && num <= 999999.99;
}

/**
 * Parse price input from form (handles string input)
 * @param value - Price input value
 * @returns Parsed price as number or null
 */
export function parsePrice(value: any): number | null {
    if (value === null || value === undefined || value === '') return null;
    const num = parseFloat(value);
    return isNaN(num) ? null : num;
}
