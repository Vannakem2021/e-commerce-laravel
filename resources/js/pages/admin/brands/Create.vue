<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { ref } from 'vue';
import { useSonnerToast } from '@/composables/useSonnerToast';

// Composables
const toast = useSonnerToast();

// Form handling
const form = useForm({
    name: '',
    slug: '',
    description: '',
    logo: null as File | null,
    website: '',
    is_active: true,
    is_featured: false,
    sort_order: 0,
    meta_title: '',
    meta_description: '',
});

// Image upload
const logoPreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement>();

// Auto-generate slug from name
const generateSlug = () => {
    if (form.name) {
        form.slug = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
};

// Handle logo upload
const handleLogoUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.logo = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    logoPreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Submit form
const submit = () => {
    form.post(route('admin.brands.store'), {
        onSuccess: () => {
            toast.success('Brand created successfully!');
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            if (firstError) {
                const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                toast.error('Failed to create brand', { description: errorMessage });
            } else {
                toast.error('Failed to create brand. Please try again.');
            }
        },
    });
};
</script>

<template>
    <Head title="Create Brand - Admin" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Brand</h1>
                    <p class="mt-1 text-sm text-gray-600">Add a new brand to your store</p>
                </div>
                <Link :href="route('admin.brands.index')">
                    <Button variant="outline">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Back to Brands
                    </Button>
                </Link>
            </div>

            <!-- Form -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>

                            <!-- Name -->
                            <div>
                                <Label for="name">Brand Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    @input="generateSlug"
                                    type="text"
                                    required
                                    class="mt-1"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Slug -->
                            <div>
                                <Label for="slug">URL Slug *</Label>
                                <Input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    required
                                    class="mt-1"
                                    :class="{ 'border-red-500': form.errors.slug }"
                                />
                                <p class="mt-1 text-sm text-gray-500">Used in URLs. Auto-generated from name.</p>
                                <div v-if="form.errors.slug" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.slug }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="mt-1"
                                    :class="{ 'border-red-500': form.errors.description }"
                                />
                                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Website -->
                            <div>
                                <Label for="website">Website URL</Label>
                                <Input
                                    id="website"
                                    v-model="form.website"
                                    type="url"
                                    placeholder="https://example.com"
                                    class="mt-1"
                                    :class="{ 'border-red-500': form.errors.website }"
                                />
                                <div v-if="form.errors.website" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.website }}
                                </div>
                            </div>
                        </div>

                        <!-- Additional Settings -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900">Additional Settings</h3>

                            <!-- Logo Upload -->
                            <div>
                                <Label for="logo">Brand Logo</Label>

                                <!-- Logo Preview -->
                                <div v-if="logoPreview" class="relative mt-2 inline-block">
                                    <img :src="logoPreview" alt="Logo preview" class="h-24 w-24 rounded-lg border object-contain" />
                                    <button
                                        type="button"
                                        @click="removeLogo"
                                        class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-xs text-white hover:bg-red-600"
                                    >
                                        ×
                                    </button>
                                </div>

                                <!-- Upload Area -->
                                <div
                                    v-else
                                    class="mt-2 rounded-lg border-2 border-dashed border-gray-300 p-4 text-center transition-colors hover:border-gray-400"
                                >
                                    <div class="mb-2 text-gray-400">
                                        <svg class="mx-auto h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                            />
                                        </svg>
                                    </div>
                                    <p class="mb-2 text-sm text-gray-600">Upload brand logo</p>
                                    <input ref="fileInput" type="file" accept="image/*" @change="handleLogoUpload" class="hidden" />
                                    <button
                                        type="button"
                                        @click="fileInput?.click()"
                                        class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200"
                                    >
                                        Choose Image
                                    </button>
                                </div>

                                <p class="mt-1 text-sm text-gray-500">Upload a logo image for this brand (PNG, JPG, SVG).</p>
                                <div v-if="form.errors.logo" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.logo }}
                                </div>
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <Label for="sort_order">Sort Order</Label>
                                <Input
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    type="number"
                                    min="0"
                                    class="mt-1"
                                    :class="{ 'border-red-500': form.errors.sort_order }"
                                />
                                <p class="mt-1 text-sm text-gray-500">Lower numbers appear first.</p>
                                <div v-if="form.errors.sort_order" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.sort_order }}
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                                />
                                <Label for="is_active">Active</Label>
                            </div>

                            <!-- Featured Status -->
                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_featured"
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                                />
                                <Label for="is_featured">Featured Brand</Label>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">SEO Settings</h3>
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <!-- Meta Title -->
                            <div>
                                <Label for="meta_title">Meta Title</Label>
                                <Input
                                    id="meta_title"
                                    v-model="form.meta_title"
                                    type="text"
                                    class="mt-1"
                                    :class="{ 'border-red-500': form.errors.meta_title }"
                                />
                                <div v-if="form.errors.meta_title" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.meta_title }}
                                </div>
                            </div>

                            <!-- Meta Description -->
                            <div>
                                <Label for="meta_description">Meta Description</Label>
                                <Textarea
                                    id="meta_description"
                                    v-model="form.meta_description"
                                    rows="3"
                                    class="mt-1"
                                    :class="{ 'border-red-500': form.errors.meta_description }"
                                />
                                <div v-if="form.errors.meta_description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.meta_description }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6">
                        <Link :href="route('admin.brands.index')">
                            <Button type="button" variant="outline">Cancel</Button>
                        </Link>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Creating...' : 'Create Brand' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
