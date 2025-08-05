<script setup lang="ts">
import ImageUpload from '@/components/admin/ImageUpload.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Brand, Category, Product, ProductTag } from '@/types/product';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Eye, Package, Save } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    product: Product;
    brands: Brand[];
    categories: Category[];
    tags: ProductTag[];
}

const props = defineProps<Props>();

// Form setup with existing product data
const form = useForm({
    name: props.product.name || '',
    slug: props.product.slug || '',
    sku: props.product.sku || '',
    short_description: props.product.short_description || '',
    description: props.product.description || '',
    features: props.product.features || '',
    specifications: props.product.specifications || '',
    price: props.product.price ? props.product.price / 100 : 0,
    compare_price: props.product.compare_price ? props.product.compare_price / 100 : null,
    cost_price: props.product.cost_price ? props.product.cost_price / 100 : null,
    stock_quantity: props.product.stock_quantity || 0,
    stock_status: props.product.stock_status || 'in_stock',
    low_stock_threshold: props.product.low_stock_threshold || 5,
    track_inventory: props.product.track_inventory ?? true,
    product_type: props.product.product_type || 'simple',
    is_digital: props.product.is_digital ?? false,
    is_virtual: props.product.is_virtual ?? false,
    requires_shipping: props.product.requires_shipping ?? true,
    status: props.product.status || 'draft',
    is_featured: props.product.is_featured ?? false,
    is_on_sale: props.product.is_on_sale ?? false,
    published_at: props.product.published_at || '',
    meta_title: props.product.meta_title || '',
    meta_description: props.product.meta_description || '',
    brand_id: props.product.brand_id || null,
    user_id: props.product.user_id || null,
    weight: props.product.weight || null,
    length: props.product.length || null,
    width: props.product.width || null,
    height: props.product.height || null,
    sort_order: props.product.sort_order || 0,
    category_ids: props.product.categories?.map((cat) => cat.id) || [],
    tag_ids: props.product.tags?.map((tag) => tag.id) || [],
    // Removed attribute-related form fields
    images: [] as File[],
    images_to_delete: [] as number[],
});

// Removed attribute initialization

// Reactive computed properties
const isSubmitting = ref(false);

// Helper function to get image URL
const getImageUrl = (image: any): string => {
    if (image.image_path) {
        // If it's already a full URL (like placeholder), use it directly
        if (image.image_path.startsWith('http')) {
            return image.image_path;
        }
        // Convert to thumbnail size for better performance in edit view
        const sizedPath = image.image_path
            .replace('/original/', '/thumbnail/')
            .replace('.jpeg', '-thumbnail.jpeg')
            .replace('.jpg', '-thumbnail.jpg')
            .replace('.png', '-thumbnail.png');
        return `/storage/${sizedPath}`;
    }
    return 'https://via.placeholder.com/150x150/e5e7eb/6b7280?text=No+Image';
};

// Convert existing images to ImageUpload format
const productImages = ref(
    (props.product.images || []).map((image) => ({
        id: image.id,
        image_path: image.image_path,
        alt_text: image.alt_text || '',
        is_primary: image.is_primary,
        sort_order: image.sort_order,
        preview: getImageUrl(image),
        name: image.image_path.split('/').pop() || 'image',
        size: 0, // Size not available for existing images
        file: undefined, // No file for existing images
    })),
);

// Auto-generate slug from name
const generateSlug = (name: string): string => {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
};

// Watch name changes to auto-generate slug (only if slug is empty or matches current name)
const updateSlug = () => {
    if (!form.slug || form.slug === generateSlug(props.product.name)) {
        form.slug = generateSlug(form.name);
    }
};

// Computed properties for form validation
const isFormValid = computed(() => {
    return form.name.trim() !== '' && form.sku.trim() !== '' && form.price >= 0;
});

// Computed properties for nullable price fields
const comparePriceValue = computed({
    get: () => form.compare_price || '',
    set: (value: string | number) => {
        form.compare_price = value === '' ? null : Number(value);
    },
});

const costPriceValue = computed({
    get: () => form.cost_price || '',
    set: (value: string | number) => {
        form.cost_price = value === '' ? null : Number(value);
    },
});

// Removed attribute-related computed properties and methods

// Handle image updates from ImageUpload component
const handleImageUpdate = (images: any[]) => {
    // Find which existing images were removed
    const currentImageIds = images.filter((img) => img.id).map((img) => img.id);
    const originalImageIds = (props.product.images || []).map((img) => img.id);
    const removedImageIds = originalImageIds.filter((id) => !currentImageIds.includes(id));

    // Update the images_to_delete array
    form.images_to_delete = removedImageIds;

    // Update the productImages
    productImages.value = images;
};

// Removed attribute helper functions

// Form submission
const submit = () => {
    if (!isFormValid.value || isSubmitting.value) return;

    isSubmitting.value = true;

    // Simplified submit without attribute handling
    const transformedData = { ...form.data() };
    transformedData.images_to_delete = form.images_to_delete;

    // Ensure boolean fields are properly converted
    transformedData.is_digital = Boolean(transformedData.is_digital);
    transformedData.is_featured = Boolean(transformedData.is_featured);
    transformedData.is_on_sale = Boolean(transformedData.is_on_sale);
    transformedData.is_virtual = Boolean(transformedData.is_virtual);
    transformedData.requires_shipping = Boolean(transformedData.requires_shipping);
    transformedData.track_inventory = Boolean(transformedData.track_inventory);

    delete (transformedData as any).existing_images;

    // Check if we have new images to upload
    const newImages = productImages.value.filter((img) => img.file);
    const hasNewImages = newImages.length > 0;

    if (hasNewImages) {
        // Create FormData for file upload
        const formData = new FormData();

        // Add all form fields to FormData
        Object.keys(transformedData).forEach((key) => {
            const value = transformedData[key as keyof typeof transformedData];
            if (value !== null && value !== undefined) {
                if (Array.isArray(value)) {
                    value.forEach((item, index) => {
                        formData.append(`${key}[${index}]`, item.toString());
                    });
                } else if (typeof value === 'object') {
                    Object.keys(value).forEach((subKey) => {
                        const subValue = (value as any)[subKey];
                        if (Array.isArray(subValue)) {
                            subValue.forEach((item, index) => {
                                formData.append(`${key}[${subKey}][${index}]`, item.toString());
                            });
                        } else {
                            formData.append(`${key}[${subKey}]`, subValue.toString());
                        }
                    });
                } else if (typeof value === 'boolean') {
                    formData.append(key, value ? '1' : '0');
                } else {
                    formData.append(key, value.toString());
                }
            }
        });

        // Add new images with metadata
        newImages.forEach((image, index) => {
            if (image.file) {
                formData.append(`images[${index}]`, image.file);
                formData.append(`image_metadata[${index}][alt_text]`, image.alt_text || '');
                formData.append(`image_metadata[${index}][is_primary]`, image.is_primary ? '1' : '0');
                formData.append(`image_metadata[${index}][sort_order]`, image.sort_order.toString());
            }
        });

        // Add method override for PUT request
        formData.append('_method', 'PUT');

        // Use Inertia router directly for FormData
        router.post(route('admin.products.update', props.product.slug), formData, {
            onSuccess: () => {
                isSubmitting.value = false;
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
                isSubmitting.value = false;
            },
        });
    } else {
        // Use regular form submission when no images with transformed data
        router.put(route('admin.products.update', props.product.slug), transformedData, {
            onSuccess: () => {
                isSubmitting.value = false;
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
                isSubmitting.value = false;
            },
        });
    }
};
</script>

<template>
    <Head :title="`Edit ${product.name} - Admin`" />

    <AdminLayout>
        <div class="container mx-auto max-w-7xl px-4 py-8">
            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('admin.products.index')">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Products
                        </Button>
                    </Link>
                    <div class="flex items-center space-x-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                            <Package class="h-6 w-6 text-purple-600" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-foreground">Edit Product</h1>
                            <p class="text-muted-foreground">{{ product.name }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Link :href="route('admin.products.show', product.slug)">
                        <Button variant="outline" size="sm">
                            <Eye class="mr-2 h-4 w-4" />
                            View Product
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-8">
                <Tabs default-value="basic" class="w-full">
                    <TabsList class="grid w-full grid-cols-3">
                        <TabsTrigger value="basic">Basic Info</TabsTrigger>
                        <TabsTrigger value="images">Images</TabsTrigger>
                        <TabsTrigger value="pricing">Pricing & Inventory</TabsTrigger>
                    </TabsList>

                    <!-- Basic Information Tab -->
                    <TabsContent value="basic" class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Product Information</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Product Name -->
                                <div class="space-y-2">
                                    <Label for="name">Product Name *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Enter product name"
                                        required
                                        @input="updateSlug"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <div v-if="form.errors.name" class="text-sm text-red-500">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <!-- Product Slug -->
                                <div class="space-y-2">
                                    <Label for="slug">Product Slug *</Label>
                                    <Input
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        placeholder="product-slug"
                                        required
                                        :class="{ 'border-red-500': form.errors.slug }"
                                    />
                                    <div v-if="form.errors.slug" class="text-sm text-red-500">
                                        {{ form.errors.slug }}
                                    </div>
                                </div>

                                <!-- SKU -->
                                <div class="space-y-2">
                                    <Label for="sku">SKU *</Label>
                                    <Input
                                        id="sku"
                                        v-model="form.sku"
                                        type="text"
                                        placeholder="Enter SKU"
                                        required
                                        :class="{ 'border-red-500': form.errors.sku }"
                                    />
                                    <div v-if="form.errors.sku" class="text-sm text-red-500">
                                        {{ form.errors.sku }}
                                    </div>
                                </div>

                                <!-- Short Description -->
                                <div class="space-y-2">
                                    <Label for="short_description">Short Description</Label>
                                    <Textarea
                                        id="short_description"
                                        v-model="form.short_description"
                                        placeholder="Brief product description"
                                        rows="3"
                                        :class="{ 'border-red-500': form.errors.short_description }"
                                    />
                                    <div v-if="form.errors.short_description" class="text-sm text-red-500">
                                        {{ form.errors.short_description }}
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Detailed product description"
                                        rows="6"
                                        :class="{ 'border-red-500': form.errors.description }"
                                    />
                                    <div v-if="form.errors.description" class="text-sm text-red-500">
                                        {{ form.errors.description }}
                                    </div>
                                </div>

                                <!-- Product Type and Status -->
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <!-- Product Type -->
                                    <div class="space-y-2">
                                        <Label for="product_type">Product Type *</Label>
                                        <Select v-model="form.product_type">
                                            <SelectTrigger :class="{ 'border-red-500': form.errors.product_type }">
                                                <SelectValue placeholder="Select product type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="simple">Simple Product</SelectItem>
                                                <SelectItem value="variable">Variable Product</SelectItem>
                                                <SelectItem value="digital">Digital Product</SelectItem>
                                                <SelectItem value="service">Service</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <div v-if="form.errors.product_type" class="text-sm text-red-500">
                                            {{ form.errors.product_type }}
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div class="space-y-2">
                                        <Label for="status">Status *</Label>
                                        <Select v-model="form.status">
                                            <SelectTrigger :class="{ 'border-red-500': form.errors.status }">
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="draft">Draft</SelectItem>
                                                <SelectItem value="published">Published</SelectItem>
                                                <SelectItem value="archived">Archived</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <div v-if="form.errors.status" class="text-sm text-red-500">
                                            {{ form.errors.status }}
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- Images Tab -->
                    <TabsContent value="images" class="space-y-6">
                        <Card class="shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-xl font-semibold text-foreground">Product Images</CardTitle>
                                <p class="text-sm text-muted-foreground">Upload high-quality images to showcase your product</p>
                            </CardHeader>
                            <CardContent>
                                <ImageUpload v-model="productImages" :max-files="10" @update:model-value="handleImageUpdate" />
                                <div v-if="form.errors.images" class="mt-4 text-sm text-red-500">
                                    {{ form.errors.images }}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- Pricing & Inventory Tab -->
                    <TabsContent value="pricing" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <!-- Pricing Section -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>Pricing</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <!-- Regular Price -->
                                    <div class="space-y-2">
                                        <Label for="price">Regular Price *</Label>
                                        <div class="relative">
                                            <span class="absolute top-1/2 left-3 -translate-y-1/2 transform text-gray-500">$</span>
                                            <Input
                                                id="price"
                                                v-model.number="form.price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                class="pl-8"
                                                required
                                                :class="{ 'border-red-500': form.errors.price }"
                                            />
                                        </div>
                                        <div v-if="form.errors.price" class="text-sm text-red-500">
                                            {{ form.errors.price }}
                                        </div>
                                    </div>

                                    <!-- Compare Price -->
                                    <div class="space-y-2">
                                        <Label for="compare_price">Compare Price</Label>
                                        <div class="relative">
                                            <span class="absolute top-1/2 left-3 -translate-y-1/2 transform text-gray-500">$</span>
                                            <Input
                                                id="compare_price"
                                                v-model="comparePriceValue"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                class="pl-8"
                                                :class="{ 'border-red-500': form.errors.compare_price }"
                                            />
                                        </div>
                                        <div v-if="form.errors.compare_price" class="text-sm text-red-500">
                                            {{ form.errors.compare_price }}
                                        </div>
                                        <p class="text-sm text-muted-foreground">Show a higher price to indicate savings</p>
                                    </div>

                                    <!-- Cost Price -->
                                    <div class="space-y-2">
                                        <Label for="cost_price">Cost Price</Label>
                                        <div class="relative">
                                            <span class="absolute top-1/2 left-3 -translate-y-1/2 transform text-gray-500">$</span>
                                            <Input
                                                id="cost_price"
                                                v-model="costPriceValue"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                placeholder="0.00"
                                                class="pl-8"
                                                :class="{ 'border-red-500': form.errors.cost_price }"
                                            />
                                        </div>
                                        <div v-if="form.errors.cost_price" class="text-sm text-red-500">
                                            {{ form.errors.cost_price }}
                                        </div>
                                        <p class="text-sm text-muted-foreground">Your cost for this product (for profit calculations)</p>
                                    </div>
                                </CardContent>
                            </Card>

                            <!-- Inventory Section -->
                            <Card>
                                <CardHeader>
                                    <CardTitle>Inventory</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <!-- Track Inventory -->
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-0.5">
                                            <Label for="track_inventory">Track Inventory</Label>
                                            <p class="text-sm text-muted-foreground">Enable inventory tracking for this product</p>
                                        </div>
                                        <Switch id="track_inventory" v-model:checked="form.track_inventory" />
                                    </div>

                                    <!-- Stock Quantity -->
                                    <div class="space-y-2">
                                        <Label for="stock_quantity">Stock Quantity *</Label>
                                        <Input
                                            id="stock_quantity"
                                            v-model.number="form.stock_quantity"
                                            type="number"
                                            min="0"
                                            placeholder="0"
                                            required
                                            :class="{ 'border-red-500': form.errors.stock_quantity }"
                                        />
                                        <div v-if="form.errors.stock_quantity" class="text-sm text-red-500">
                                            {{ form.errors.stock_quantity }}
                                        </div>
                                    </div>

                                    <!-- Stock Status -->
                                    <div class="space-y-2">
                                        <Label for="stock_status">Stock Status *</Label>
                                        <Select v-model="form.stock_status">
                                            <SelectTrigger :class="{ 'border-red-500': form.errors.stock_status }">
                                                <SelectValue placeholder="Select stock status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="in_stock">In Stock</SelectItem>
                                                <SelectItem value="out_of_stock">Out of Stock</SelectItem>
                                                <SelectItem value="back_order">Back Order</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <div v-if="form.errors.stock_status" class="text-sm text-red-500">
                                            {{ form.errors.stock_status }}
                                        </div>
                                    </div>

                                    <!-- Low Stock Threshold -->
                                    <div class="space-y-2">
                                        <Label for="low_stock_threshold">Low Stock Threshold</Label>
                                        <Input
                                            id="low_stock_threshold"
                                            v-model.number="form.low_stock_threshold"
                                            type="number"
                                            min="0"
                                            placeholder="5"
                                            :class="{ 'border-red-500': form.errors.low_stock_threshold }"
                                        />
                                        <div v-if="form.errors.low_stock_threshold" class="text-sm text-red-500">
                                            {{ form.errors.low_stock_threshold }}
                                        </div>
                                        <p class="text-sm text-muted-foreground">Get notified when stock falls below this number</p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Product Flags -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Product Flags</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <!-- Digital Product -->
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-0.5">
                                            <Label for="is_digital">Digital Product</Label>
                                            <p class="text-sm text-muted-foreground">This product is downloadable/digital</p>
                                        </div>
                                        <Switch id="is_digital" v-model:checked="form.is_digital" />
                                    </div>

                                    <!-- Virtual Product -->
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-0.5">
                                            <Label for="is_virtual">Virtual Product</Label>
                                            <p class="text-sm text-muted-foreground">This product is virtual (no physical form)</p>
                                        </div>
                                        <Switch id="is_virtual" v-model:checked="form.is_virtual" />
                                    </div>

                                    <!-- Requires Shipping -->
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-0.5">
                                            <Label for="requires_shipping">Requires Shipping</Label>
                                            <p class="text-sm text-muted-foreground">This product needs to be shipped</p>
                                        </div>
                                        <Switch id="requires_shipping" v-model:checked="form.requires_shipping" />
                                    </div>

                                    <!-- Featured Product -->
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-0.5">
                                            <Label for="is_featured">Featured Product</Label>
                                            <p class="text-sm text-muted-foreground">Show this product in featured sections</p>
                                        </div>
                                        <Switch id="is_featured" v-model:checked="form.is_featured" />
                                    </div>

                                    <!-- On Sale -->
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-0.5">
                                            <Label for="is_on_sale">On Sale</Label>
                                            <p class="text-sm text-muted-foreground">Mark this product as on sale</p>
                                        </div>
                                        <Switch id="is_on_sale" v-model:checked="form.is_on_sale" />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- Removed Attributes Tab -->
                </Tabs>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-4">
                    <Link :href="route('admin.products.index')">
                        <Button variant="outline" type="button">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="!isFormValid || isSubmitting" class="min-w-[120px]">
                        <Save class="mr-2 h-4 w-4" />
                        {{ isSubmitting ? 'Updating...' : 'Update Product' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
