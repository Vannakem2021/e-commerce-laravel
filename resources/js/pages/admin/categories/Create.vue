<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Category } from '@/types/product';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, FolderPlus, Save } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useSonnerToast } from '@/composables/useSonnerToast';

interface Props {
    parentCategories: Category[];
}

const props = defineProps<Props>();

// Composables
const toast = useSonnerToast();

// Form setup
const form = useForm({
    name: '',
    slug: '',
    description: '',
    parent_id: null as number | null,
    is_active: true,
    is_featured: false,
    sort_order: 0,
    meta_title: '',
    meta_description: '',
    image: null as File | null,
    icon: '',
});

// Image upload
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement>();

// Computed properties
const isSubcategory = computed(() => form.parent_id !== null);

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

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.image = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('admin.categories.store'), {
        onSuccess: () => {
            toast.success('Category created successfully!');
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            if (firstError) {
                const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                toast.error('Failed to create category', { description: errorMessage });
            } else {
                toast.error('Failed to create category. Please try again.');
            }
        },
    });
};
</script>

<template>
    <Head title="Create Category - Admin" />

    <AdminLayout>
        <div class="container mx-auto max-w-4xl px-4 py-4 sm:px-6 sm:py-8">
            <!-- Page Header -->
            <div class="mb-6 sm:mb-8">
                <!-- Back Button -->
                <div class="mb-4">
                    <Link :href="route('admin.categories.index')" class="flex items-center space-x-2 text-muted-foreground hover:text-foreground">
                        <ArrowLeft class="h-4 w-4" />
                        <span>Back to Categories</span>
                    </Link>
                </div>

                <!-- Header Content -->
                <div class="flex items-center space-x-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 sm:h-12 sm:w-12">
                        <FolderPlus class="h-5 w-5 text-blue-600 sm:h-6 sm:w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground sm:text-3xl">Create Category</h1>
                        <p class="text-sm text-muted-foreground sm:text-base">Add a new category to organize your products</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Basic Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Basic Information</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="name">Category Name *</Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            @blur="generateSlug"
                                            :class="{ 'border-destructive': form.errors.name }"
                                        />
                                        <p v-if="form.errors.name" class="text-sm text-destructive">
                                            {{ form.errors.name }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="slug">Slug</Label>
                                        <Input id="slug" v-model="form.slug" :class="{ 'border-destructive': form.errors.slug }" />
                                        <p v-if="form.errors.slug" class="text-sm text-destructive">
                                            {{ form.errors.slug }}
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="4"
                                        placeholder="Enter category description..."
                                        :class="{ 'border-destructive': form.errors.description }"
                                    />
                                    <p v-if="form.errors.description" class="text-sm text-destructive">
                                        {{ form.errors.description }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- SEO Settings -->
                        <Card>
                            <CardHeader>
                                <CardTitle>SEO Settings</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="meta_title">Meta Title</Label>
                                    <Input
                                        id="meta_title"
                                        v-model="form.meta_title"
                                        placeholder="SEO title for this category"
                                        :class="{ 'border-destructive': form.errors.meta_title }"
                                    />
                                    <p v-if="form.errors.meta_title" class="text-sm text-destructive">
                                        {{ form.errors.meta_title }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="meta_description">Meta Description</Label>
                                    <Textarea
                                        id="meta_description"
                                        v-model="form.meta_description"
                                        rows="3"
                                        placeholder="SEO description for this category"
                                        :class="{ 'border-destructive': form.errors.meta_description }"
                                    />
                                    <p v-if="form.errors.meta_description" class="text-sm text-destructive">
                                        {{ form.errors.meta_description }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Category Settings -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Category Settings</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="parent_id">Parent Category</Label>
                                    <Select v-model="form.parent_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select parent category (optional)" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem :value="null">No Parent (Root Category)</SelectItem>
                                            <SelectItem v-for="category in parentCategories" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.parent_id" class="text-sm text-destructive">
                                        {{ form.errors.parent_id }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="sort_order">Sort Order</Label>
                                    <Input
                                        id="sort_order"
                                        v-model.number="form.sort_order"
                                        type="number"
                                        min="0"
                                        :class="{ 'border-destructive': form.errors.sort_order }"
                                    />
                                    <p v-if="form.errors.sort_order" class="text-sm text-destructive">
                                        {{ form.errors.sort_order }}
                                    </p>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Switch id="is_active" v-model="form.is_active" />
                                    <Label for="is_active">Active</Label>
                                </div>
                                <p v-if="form.errors.is_active" class="text-sm text-destructive">
                                    {{ form.errors.is_active }}
                                </p>

                                <div class="flex items-center space-x-2">
                                    <Switch id="is_featured" v-model="form.is_featured" />
                                    <Label for="is_featured">Featured</Label>
                                </div>
                                <p v-if="form.errors.is_featured" class="text-sm text-destructive">
                                    {{ form.errors.is_featured }}
                                </p>

                                <div class="space-y-2">
                                    <Label for="icon">Icon (emoji or text)</Label>
                                    <Input
                                        id="icon"
                                        v-model="form.icon"
                                        placeholder="📱 or smartphone"
                                        :class="{ 'border-destructive': form.errors.icon }"
                                    />
                                    <p v-if="form.errors.icon" class="text-sm text-destructive">
                                        {{ form.errors.icon }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Category Image -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Category Image</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <!-- Image Preview -->
                                <div v-if="imagePreview" class="relative">
                                    <img :src="imagePreview" alt="Category preview" class="h-32 w-full rounded-lg object-cover" />
                                    <Button type="button" variant="destructive" size="sm" class="absolute top-2 right-2" @click="removeImage">
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>

                                <!-- Upload Area -->
                                <div v-else class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center">
                                    <Upload class="mx-auto mb-2 h-8 w-8 text-gray-400" />
                                    <p class="mb-2 text-sm text-gray-600">Upload category image</p>
                                    <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
                                    <Button type="button" variant="outline" @click="fileInput?.click()"> Choose Image </Button>
                                </div>
                                <p v-if="form.errors.image" class="text-sm text-destructive">
                                    {{ form.errors.image }}
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Actions -->
                        <Card>
                            <CardContent class="pt-6">
                                <Button type="submit" class="w-full" :disabled="form.processing">
                                    <Save class="mr-2 h-4 w-4" />
                                    Create Category
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
