<script setup lang="ts">
// Removed AttributeInput import
import ImageUpload from '@/components/admin/ImageUpload.vue';
import { useErrorHandler } from '@/composables/useErrorHandler';
import { useSonnerToast } from '@/composables/useSonnerToast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Brand, Category, ProductTag } from '@/types/product';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Package, Save } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    brands: Brand[];
    categories: Category[];
    tags: ProductTag[];
}

const props = defineProps<Props>();

// Composables
const errorHandler = useErrorHandler();
const toast = useSonnerToast();

// Form setup
const form = useForm({
    name: '',
    slug: '',
    sku: '',
    short_description: '',
    description: '',
    features: '',
    specifications: '',
    price: 0,
    compare_price: null as number | null,
    cost_price: null as number | null,
    stock_quantity: 0,
    stock_status: 'in_stock' as 'in_stock' | 'out_of_stock' | 'back_order',
    low_stock_threshold: 5,
    track_inventory: true,
    product_type: 'simple' as 'simple' | 'variable' | 'digital' | 'service',
    is_digital: false,
    is_virtual: false,
    requires_shipping: true,
    status: 'draft' as 'draft' | 'published' | 'archived',
    is_featured: false,
    is_on_sale: false,
    published_at: '',
    meta_title: '',
    meta_description: '',
    brand_id: null as number | null,
    weight: null as number | null,
    length: null as number | null,
    width: null as number | null,
    height: null as number | null,
    sort_order: 0,
    category_ids: [] as number[],
    tag_ids: [] as number[],
    // Removed attribute_values
});

// Image upload state
interface ProductImage {
    id?: number;
    image_path: string;
    alt_text?: string;
    is_primary: boolean;
    sort_order: number;
    file?: File;
    preview?: string;
}

const productImages = ref<ProductImage[]>([]);

// Computed properties
const isPhysicalProduct = computed(() => !form.is_digital && !form.is_virtual);

// Track if fields need auto-generation
const needsSlugGeneration = computed(() => !form.slug && form.name);
const needsSkuGeneration = computed(() => !form.sku && form.name);

// Removed attribute-related computed properties

// Methods
const generateSlug = () => {
    if (form.name) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
};

const generateSKU = () => {
    if (form.name) {
        const prefix = form.name.substring(0, 3).toUpperCase();
        const timestamp = Date.now().toString().slice(-6);
        form.sku = `${prefix}-${timestamp}`;
    }
};

// Auto-generate slug and SKU when name changes (if they're empty)
const autoGenerateFields = () => {
    if (form.name) {
        // Auto-generate slug if empty
        if (!form.slug) {
            generateSlug();
        }
        // Auto-generate SKU if empty
        if (!form.sku) {
            generateSKU();
        }
    }
};

// Form validation - only called during explicit form submission
const validateForm = () => {
    const errors = [];

    if (!form.name.trim()) {
        errors.push('Product name is required');
    }

    if (!form.slug.trim()) {
        errors.push('URL slug is required');
    }

    if (!form.sku.trim()) {
        errors.push('SKU is required');
    }

    if (form.price < 0) {
        errors.push('Price must be 0 or greater');
    }

    return errors;
};

// Show validation errors using toast notifications
const showValidationErrors = (errors: string[]) => {
    errors.forEach((error, index) => {
        // Show each error with a slight delay to avoid overwhelming the user
        setTimeout(() => {
            toast.error('Validation Error', { description: error });
        }, index * 100);
    });
};

// Check if form has basic required fields (for UI feedback, not validation)
const hasRequiredFields = computed(() => {
    return form.name.trim() !== '' && form.price >= 0;
});

const submit = () => {
    // Auto-generate missing fields before validation
    autoGenerateFields();

    // Validate form
    const validationErrors = validateForm();
    if (validationErrors.length > 0) {
        // Show validation errors to user using a more user-friendly approach
        showValidationErrors(validationErrors);
        return;
    }

    // Simplified submit without attribute validation
    const transformedData = { ...form.data() };

    // Check if we have images to upload
    const hasImages = productImages.value.some((image) => image.file);

    if (hasImages) {
        // Use Inertia router for FormData submission
        const formData = new FormData();

        // Add all form fields using transformed data
        Object.keys(transformedData).forEach((key) => {
            const value = transformedData[key];
            if (value !== null && value !== undefined) {
                if (Array.isArray(value)) {
                    value.forEach((item, index) => {
                        formData.append(`${key}[${index}]`, item);
                    });
                } else {
                    // Convert boolean values to strings for Laravel validation
                    const formValue = typeof value === 'boolean' ? (value ? '1' : '0') : value;
                    formData.append(key, formValue);
                }
            }
        });

        // Add images
        productImages.value.forEach((image, index) => {
            if (image.file) {
                formData.append(`images[${index}]`, image.file);
                formData.append(`image_metadata[${index}][alt_text]`, image.alt_text || '');
                formData.append(`image_metadata[${index}][is_primary]`, image.is_primary ? '1' : '0');
                formData.append(`image_metadata[${index}][sort_order]`, index.toString());
            }
        });

        // Use Inertia router directly for FormData
        router.post(route('admin.products.store'), formData, {
            onSuccess: (page) => {
                // Clear form on success
                form.reset();
                productImages.value = [];
                toast.success('Product created successfully!');
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
                handleFormErrors(errors);
            },
        });
    } else {
        // Use regular form submission when no images with transformed data
        router.post(route('admin.products.store'), transformedData, {
            onSuccess: (page) => {
                // Clear form on success
                form.reset();
                productImages.value = [];
                toast.success('Product created successfully!');
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
                handleFormErrors(errors);
            },
        });
    }
};



// Enhanced error handling for server-side validation errors
const handleFormErrors = (errors: Record<string, string | string[]>) => {
    // Use the standard error handler for validation errors
    errorHandler.handleValidationError(errors);

    // Scroll to first error field
    const firstErrorField = Object.keys(errors)[0];
    if (firstErrorField) {
        const element = document.getElementById(firstErrorField);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            element.focus();
        }
    }
};

const saveDraft = () => {
    form.status = 'draft';
    submit();
};

const publish = () => {
    form.status = 'published';
    form.published_at = new Date().toISOString().slice(0, 16);
    submit();
};

// Prevent accidental form submission on Enter key
const handleEnterKey = (event) => {
    // Only allow Enter to submit if the user is focused on a submit button
    const target = event.target;
    if (target && (target.type === 'submit' || target.classList.contains('submit-button'))) {
        submit();
    }
    // Otherwise, prevent the default form submission
};
</script>

<template>
    <Head title="Create Product - Admin" />

    <AdminLayout>
        <div class="container mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8">
                <!-- Back Button -->
                <div class="mb-4">
                    <Link :href="route('admin.products.index')">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Products
                        </Button>
                    </Link>
                </div>

                <!-- Enhanced Header Content -->
                <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <div class="flex items-center space-x-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 sm:h-14 sm:w-14">
                            <Package class="h-6 w-6 text-primary sm:h-7 sm:w-7" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-foreground sm:text-3xl">Create New Product</h1>
                            <p class="text-sm text-muted-foreground sm:text-base">Add a new product to your store inventory</p>
                        </div>
                    </div>

                    <!-- Enhanced Action Buttons -->
                    <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-3">
                        <Button type="button" @click="saveDraft" variant="outline" :disabled="form.processing" class="w-full font-medium sm:w-auto" size="lg">
                            Save Draft
                        </Button>
                        <Button type="button" @click="publish" :disabled="form.processing" class="w-full font-medium sm:w-auto" size="lg">
                            <Save class="mr-2 h-4 w-4" />
                            Publish Product
                        </Button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" @keydown.enter.prevent="handleEnterKey">
                <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-8 xl:col-span-2">
                        <!-- Enhanced Basic Information -->
                        <Card class="shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-xl font-semibold text-foreground">Basic Information</CardTitle>
                                <p class="text-sm text-muted-foreground">Essential product details and descriptions</p>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div class="space-y-3">
                                        <Label for="name" class="text-sm font-medium text-foreground">Product Name *</Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            @blur="autoGenerateFields"
                                            :class="{ 'border-destructive': form.errors.name }"
                                            class="transition-colors focus:border-primary"
                                            placeholder="Enter product name"
                                        />
                                        <p v-if="form.errors.name" class="text-sm text-destructive">
                                            {{ form.errors.name }}
                                        </p>
                                    </div>
                                    <div class="space-y-3">
                                        <Label for="slug" class="text-sm font-medium text-foreground">URL Slug *</Label>
                                        <Input
                                            id="slug"
                                            v-model="form.slug"
                                            :class="{ 'border-destructive': form.errors.slug }"
                                            class="transition-colors focus:border-primary"
                                            placeholder="auto-generated-from-name"
                                        />
                                        <p v-if="form.errors.slug" class="text-sm text-destructive">
                                            {{ form.errors.slug }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">URL slug will be auto-generated from product name if left empty</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <Label for="short_description" class="text-sm font-medium text-foreground">Short Description</Label>
                                    <Textarea
                                        id="short_description"
                                        v-model="form.short_description"
                                        rows="3"
                                        :class="{ 'border-destructive': form.errors.short_description }"
                                        class="resize-none transition-colors focus:border-primary"
                                        placeholder="Brief product summary for listings and previews"
                                    />
                                    <p v-if="form.errors.short_description" class="text-sm text-destructive">
                                        {{ form.errors.short_description }}
                                    </p>
                                </div>

                                <div class="space-y-3">
                                    <Label for="description" class="text-sm font-medium text-foreground">Full Description</Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="6"
                                        :class="{ 'border-destructive': form.errors.description }"
                                        class="resize-none transition-colors focus:border-primary"
                                        placeholder="Detailed product description with features and benefits"
                                    />
                                    <p v-if="form.errors.description" class="text-sm text-destructive">
                                        {{ form.errors.description }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Enhanced Product Images -->
                        <Card class="shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-xl font-semibold text-foreground">Product Images</CardTitle>
                                <p class="text-sm text-muted-foreground">Upload high-quality images to showcase your product</p>
                            </CardHeader>
                            <CardContent>
                                <ImageUpload v-model="productImages" :max-files="10" />
                            </CardContent>
                        </Card>

                        <!-- Removed Product Attributes section -->

                        <!-- Enhanced Pricing & Inventory -->
                        <Card class="shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-xl font-semibold text-foreground">Pricing & Inventory</CardTitle>
                                <p class="text-sm text-muted-foreground">Set pricing and manage stock levels</p>
                            </CardHeader>
                            <CardContent>
                                <Tabs default-value="pricing" class="w-full">
                                    <TabsList class="grid w-full grid-cols-2">
                                        <TabsTrigger value="pricing">Pricing</TabsTrigger>
                                        <TabsTrigger value="inventory">Inventory</TabsTrigger>
                                    </TabsList>

                                    <TabsContent value="pricing" class="space-y-4">
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                            <div class="space-y-2">
                                                <Label for="price">Price *</Label>
                                                <Input
                                                    id="price"
                                                    v-model.number="form.price"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    :class="{ 'border-destructive': form.errors.price }"
                                                />
                                                <p v-if="form.errors.price" class="text-sm text-destructive">
                                                    {{ form.errors.price }}
                                                </p>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="compare_price">Compare Price</Label>
                                                <Input id="compare_price" v-model.number="form.compare_price" type="number" step="0.01" min="0" />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="cost_price">Cost Price</Label>
                                                <Input id="cost_price" v-model.number="form.cost_price" type="number" step="0.01" min="0" />
                                            </div>
                                        </div>
                                    </TabsContent>

                                    <TabsContent value="inventory" class="space-y-4">
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div class="space-y-2">
                                                <Label for="sku" class="text-sm font-medium text-foreground">SKU (Stock Keeping Unit) *</Label>
                                                <div class="flex space-x-2">
                                                    <Input
                                                        id="sku"
                                                        v-model="form.sku"
                                                        :class="{ 'border-destructive': form.errors.sku }"
                                                        class="transition-colors focus:border-primary"
                                                        placeholder="Auto-generated or enter custom SKU"
                                                    />
                                                    <Button type="button" @click="generateSKU" variant="outline" size="sm" class="whitespace-nowrap">
                                                        Generate
                                                    </Button>
                                                </div>
                                                <p v-if="form.errors.sku" class="text-sm text-destructive">
                                                    {{ form.errors.sku }}
                                                </p>
                                                <p class="text-xs text-muted-foreground">
                                                    SKU will be auto-generated from product name if left empty
                                                </p>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="stock_status">Stock Status</Label>
                                                <Select v-model="form.stock_status">
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="in_stock">In Stock</SelectItem>
                                                        <SelectItem value="out_of_stock">Out of Stock</SelectItem>
                                                        <SelectItem value="back_order">Back Order</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div class="space-y-2">
                                                <Label for="stock_quantity">Stock Quantity</Label>
                                                <Input id="stock_quantity" v-model.number="form.stock_quantity" type="number" min="0" />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="low_stock_threshold">Low Stock Threshold</Label>
                                                <Input id="low_stock_threshold" v-model.number="form.low_stock_threshold" type="number" min="0" />
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <Switch id="track_inventory" v-model:checked="form.track_inventory" />
                                            <Label for="track_inventory">Track inventory</Label>
                                        </div>
                                    </TabsContent>
                                </Tabs>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Enhanced Sidebar -->
                    <div class="space-y-8">
                        <!-- Enhanced Product Settings -->
                        <Card class="shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-xl font-semibold text-foreground">Product Settings</CardTitle>
                                <p class="text-sm text-muted-foreground">Configure product status and type</p>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <div class="space-y-2">
                                    <Label for="status">Status</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="draft">Draft</SelectItem>
                                            <SelectItem value="published">Published</SelectItem>
                                            <SelectItem value="archived">Archived</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label for="product_type">Product Type</Label>
                                    <Select v-model="form.product_type">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="simple">Simple</SelectItem>
                                            <SelectItem value="variable">Variable</SelectItem>
                                            <SelectItem value="digital">Digital</SelectItem>
                                            <SelectItem value="service">Service</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center space-x-2">
                                        <Switch id="is_featured" v-model:checked="form.is_featured" />
                                        <Label for="is_featured">Featured Product</Label>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <Switch id="is_on_sale" v-model:checked="form.is_on_sale" />
                                        <Label for="is_on_sale">On Sale</Label>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <Switch id="is_digital" v-model:checked="form.is_digital" />
                                        <Label for="is_digital">Digital Product</Label>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <Switch id="is_virtual" v-model:checked="form.is_virtual" />
                                        <Label for="is_virtual">Virtual Product</Label>
                                    </div>

                                    <div v-if="isPhysicalProduct" class="flex items-center space-x-2">
                                        <Switch id="requires_shipping" v-model:checked="form.requires_shipping" />
                                        <Label for="requires_shipping">Requires Shipping</Label>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Enhanced Organization -->
                        <Card class="shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader class="pb-4">
                                <CardTitle class="text-xl font-semibold text-foreground">Organization</CardTitle>
                                <p class="text-sm text-muted-foreground">Categorize and organize your product</p>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <div class="space-y-2">
                                    <Label for="brand_id">Brand</Label>
                                    <Select v-model="form.brand_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select brand" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="brand in brands" :key="brand.id" :value="brand.id">
                                                {{ brand.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label>Categories</Label>
                                    <div class="max-h-40 space-y-2 overflow-y-auto">
                                        <div v-for="category in categories" :key="category.id" class="flex items-center space-x-2">
                                            <input
                                                :id="`category-${category.id}`"
                                                v-model="form.category_ids"
                                                :value="category.id"
                                                type="checkbox"
                                                class="rounded border-gray-300"
                                            />
                                            <Label :for="`category-${category.id}`" class="text-sm">
                                                {{ category.name }}
                                            </Label>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
