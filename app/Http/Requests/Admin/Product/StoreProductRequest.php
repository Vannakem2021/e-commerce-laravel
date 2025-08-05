<?php

namespace App\Http\Requests\Admin\Product;

// Removed ProductAttribute import
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get validated data with price conversion to cents.
     */
    public function getValidatedDataWithPriceConversion(): array
    {
        $data = $this->validated();

        // Convert dollar amounts to cents for storage
        if (isset($data['price'])) {
            $data['price'] = round($data['price'] * 100);
        }

        if (isset($data['compare_price'])) {
            $data['compare_price'] = round($data['compare_price'] * 100);
        }

        if (isset($data['cost_price'])) {
            $data['cost_price'] = round($data['cost_price'] * 100);
        }

        return $data;
    }

    // Removed attribute validation

    // Removed attribute validation methods

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:280', 'unique:products,slug'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'string'],
            'specifications' => ['nullable', 'array'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'compare_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99', 'gt:price'],
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'stock_status' => ['required', Rule::in(['in_stock', 'out_of_stock', 'back_order'])],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['nullable', 'boolean'],
            'product_type' => ['required', Rule::in(['simple', 'variable', 'digital', 'service'])],
            'is_digital' => ['nullable', 'boolean'],
            'is_virtual' => ['nullable', 'boolean'],
            'requires_shipping' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'is_featured' => ['nullable', 'boolean'],
            'is_on_sale' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'seo_data' => ['nullable', 'array'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'additional_data' => ['nullable', 'array'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'primary_category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:product_tags,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'], // 10MB max
            'image_metadata' => ['nullable', 'array'],
            'image_metadata.*.alt_text' => ['nullable', 'string', 'max:255'],
            'image_metadata.*.is_primary' => ['nullable', 'boolean'],
            'image_metadata.*.sort_order' => ['nullable', 'integer', 'min:0'],
            // Removed attribute validation rules
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'slug.required' => 'URL slug is required. It will be auto-generated if you leave it empty.',
            'slug.unique' => 'This URL slug is already taken. Please choose a different one.',
            'sku.required' => 'SKU (Stock Keeping Unit) is required. Click "Generate" or it will be auto-generated.',
            'sku.unique' => 'This SKU is already taken. Please choose a different one.',
            'price.required' => 'Product price is required.',
            'price.min' => 'Product price must be greater than 0.',
            'compare_price.gt' => 'Compare price must be greater than the regular price.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'categories.*.exists' => 'One or more selected categories do not exist.',
            'brand_id.exists' => 'The selected brand does not exist.',
        ];
    }


}
