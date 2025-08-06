<?php

namespace App\Http\Requests\Admin\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $brandId = $this->brand->id ?? null;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('brands', 'name')->ignore($brandId)],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('brands', 'slug')->ignore($brandId)],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Brand name is required.',
            'name.unique' => 'This brand name is already taken.',
            'slug.required' => 'URL slug is required.',
            'slug.unique' => 'This slug is already taken.',
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a JPEG, PNG, JPG, GIF, or SVG file.',
            'logo.max' => 'Logo file size cannot exceed 2MB.',
            'website.url' => 'Please enter a valid website URL.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'meta_title.max' => 'Meta title cannot exceed 160 characters.',
            'meta_description.max' => 'Meta description cannot exceed 320 characters.',
            'sort_order.min' => 'Sort order must be at least 0.',
            'sort_order.max' => 'Sort order cannot exceed 9999.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if not provided
        if (!$this->slug && $this->name) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name),
            ]);
        }

        // Set default sort order if not provided
        if (!$this->sort_order) {
            $this->merge([
                'sort_order' => 0,
            ]);
        }
    }
}
