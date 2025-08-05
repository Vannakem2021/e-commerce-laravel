<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVariantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage-products');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sku' => [
                'nullable',
                'string',
                'max:100',
                'unique:product_variants,sku',
            ],
            'name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'compare_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                'gt:price',
            ],
            'cost_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
                'max:999999',
            ],
            'stock_status' => [
                'required',
                'string',
                Rule::in(['in_stock', 'out_of_stock', 'back_order']),
            ],
            'low_stock_threshold' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999',
            ],
            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'length' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'width' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'height' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'is_active' => [
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048', // 2MB
            ],

        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sku.unique' => 'A variant with this SKU already exists.',
            'price.min' => 'Price must be at least $0.00.',
            'price.max' => 'Price cannot exceed $999,999.99.',
            'compare_price.gt' => 'Compare price must be greater than the regular price.',
            'compare_price.max' => 'Compare price cannot exceed $999,999.99.',
            'cost_price.min' => 'Cost price must be at least $0.00.',
            'cost_price.max' => 'Cost price cannot exceed $999,999.99.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'stock_quantity.min' => 'Stock quantity must be at least 0.',
            'stock_quantity.max' => 'Stock quantity cannot exceed 999,999.',
            'stock_status.required' => 'Stock status is required.',
            'stock_status.in' => 'Invalid stock status selected.',
            'low_stock_threshold.min' => 'Low stock threshold must be at least 0.',
            'low_stock_threshold.max' => 'Low stock threshold cannot exceed 999,999.',
            'weight.min' => 'Weight must be at least 0.',
            'weight.max' => 'Weight cannot exceed 999,999.99.',
            'length.min' => 'Length must be at least 0.',
            'length.max' => 'Length cannot exceed 999,999.99.',
            'width.min' => 'Width must be at least 0.',
            'width.max' => 'Width cannot exceed 999,999.99.',
            'height.min' => 'Height must be at least 0.',
            'height.max' => 'Height cannot exceed 999,999.99.',
            'sort_order.min' => 'Sort order must be at least 0.',
            'sort_order.max' => 'Sort order cannot exceed 999,999.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The image may not be greater than 2MB.',
            'attribute_values.*.required_with' => 'All attribute values must be provided.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string booleans to actual booleans
        $this->merge([
            'is_active' => $this->boolean('is_active', true), // Default to true
        ]);

        // Set default values
        if (!$this->has('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }

        if (!$this->has('low_stock_threshold')) {
            $this->merge(['low_stock_threshold' => 5]);
        }

        // Auto-set stock status based on quantity if not provided
        if (!$this->has('stock_status') && $this->has('stock_quantity')) {
            $stockStatus = $this->get('stock_quantity') > 0 ? 'in_stock' : 'out_of_stock';
            $this->merge(['stock_status' => $stockStatus]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = $this->route('product');
            
            // Validate that attribute combination is unique for this product
            if ($this->has('attribute_values') && !empty($this->get('attribute_values'))) {
                $attributeValues = $this->get('attribute_values');
                
                $existingVariant = $product->variants()
                    ->get()
                    ->first(function ($variant) use ($attributeValues) {
                        if (!$variant->attribute_values) {
                            return false;
                        }
                        
                        // Check if attribute combinations match exactly
                        return $variant->attribute_values == $attributeValues;
                    });
                
                if ($existingVariant) {
                    $validator->errors()->add('attribute_values', 'A variant with this attribute combination already exists for this product.');
                }
            }

            // Validate stock status consistency
            if ($this->get('stock_status') === 'in_stock' && $this->get('stock_quantity') == 0) {
                $validator->errors()->add('stock_status', 'Stock status cannot be "in stock" when quantity is 0.');
            }
        });
    }
}
