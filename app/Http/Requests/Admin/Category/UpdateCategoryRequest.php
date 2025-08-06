<?php

namespace App\Http\Requests\Admin\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug from name if slug is empty but name is provided
        if (!$this->slug && $this->name) {
            $categoryId = $this->route('category');
            $categoryId = is_object($categoryId) ? $categoryId->id : $categoryId;

            $baseSlug = Str::slug($this->name);
            $slug = $this->ensureUniqueSlug($baseSlug, $categoryId);

            $this->merge([
                'slug' => $slug
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\-_&()]+$/'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($categoryId), 'regex:/^[a-z0-9\-]+$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'meta_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'icon' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9\-]+$/'],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if ($value && $this->route('category')) {
                        $category = $this->route('category');
                        $categoryId = is_object($category) ? $category->id : $category;

                        if ($this->wouldCreateCircularReference($categoryId, $value)) {
                            $fail('Cannot create circular reference in category hierarchy.');
                        }
                    }
                }
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'seo_data' => ['nullable', 'array', 'max:10'],
            'seo_data.*' => ['string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.regex' => 'Category name contains invalid characters.',
            'slug.unique' => 'This slug is already taken.',
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
            'description.max' => 'Description cannot exceed 2000 characters.',
            'meta_description.max' => 'Meta description cannot exceed 320 characters.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'image.max' => 'Image size cannot exceed 2MB.',
            'icon.regex' => 'Icon name can only contain lowercase letters, numbers, and hyphens.',
            'sort_order.max' => 'Sort order cannot exceed 9999.',
            'seo_data.max' => 'SEO data cannot have more than 10 fields.',
            'seo_data.*.max' => 'Each SEO data field cannot exceed 255 characters.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ];
    }

    /**
     * Check if setting a parent would create a circular reference.
     * This traverses up the parent hierarchy to detect cycles.
     */
    protected function wouldCreateCircularReference(int $categoryId, int $parentId): bool
    {
        // If trying to set self as parent, that's obviously circular
        if ($categoryId === $parentId) {
            return true;
        }

        // Traverse up the parent hierarchy to check for cycles
        $current = Category::find($parentId);
        $visited = [$parentId]; // Track visited nodes to prevent infinite loops

        while ($current && $current->parent_id) {
            // If we encounter the category we're trying to update, it's circular
            if ($current->parent_id === $categoryId) {
                return true;
            }

            // If we've seen this parent before, we have a cycle (shouldn't happen with valid data)
            if (in_array($current->parent_id, $visited)) {
                return true;
            }

            $visited[] = $current->parent_id;
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Ensure the slug is unique by appending a number if necessary.
     */
    protected function ensureUniqueSlug(string $baseSlug, int $excludeId = null): string
    {
        $slug = $baseSlug;
        $counter = 1;

        $query = Category::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;

            $query = Category::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
