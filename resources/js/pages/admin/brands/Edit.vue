<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { type Brand } from '@/types/product';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Trash2 } from 'lucide-vue-next';

interface Props {
    brand: Brand;
}

const props = defineProps<Props>();

// Form handling
const form = useForm({
    name: props.brand.name,
    slug: props.brand.slug,
    description: props.brand.description || '',
    logo: null as File | null,
    website: props.brand.website || '',
    is_active: props.brand.is_active,
    sort_order: props.brand.sort_order,
    meta_title: props.brand.meta_title || '',
    meta_description: props.brand.meta_description || '',
});

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
    if (target.files && target.files[0]) {
        form.logo = target.files[0];
    }
};

// Submit form
const submit = () => {
    form.put(route('admin.brands.update', props.brand.id), {
        onSuccess: () => {
            // Redirect handled by backend
        },
    });
};

// Delete brand
const deleteBrand = () => {
    if (confirm('Are you sure you want to delete this brand? This action cannot be undone.')) {
        router.delete(route('admin.brands.destroy', props.brand.id));
    }
};
</script>

<template>
    <Head :title="`Edit ${brand.name} - Admin`" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Brand</h1>
                    <p class="mt-1 text-sm text-gray-600">Update brand information</p>
                </div>
                <div class="flex space-x-3">
                    <Button @click="deleteBrand" variant="destructive">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                    <Link :href="route('admin.brands.index')">
                        <Button variant="outline">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Back to Brands
                        </Button>
                    </Link>
                </div>
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

                            <!-- Current Logo -->
                            <div v-if="brand.logo">
                                <Label>Current Logo</Label>
                                <div class="mt-1">
                                    <img :src="brand.logo" :alt="brand.name" class="h-16 w-auto rounded border" />
                                </div>
                            </div>

                            <!-- Logo Upload -->
                            <div>
                                <Label for="logo">{{ brand.logo ? 'Replace Logo' : 'Brand Logo' }}</Label>
                                <input
                                    id="logo"
                                    type="file"
                                    accept="image/*"
                                    @change="handleLogoUpload"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-gray-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-100"
                                />
                                <p class="mt-1 text-sm text-gray-500">Upload a logo image for this brand.</p>
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
                            {{ form.processing ? 'Updating...' : 'Update Brand' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
