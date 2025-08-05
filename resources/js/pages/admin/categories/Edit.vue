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
import { ArrowLeft, Edit, Save } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    category: Category;
    parentCategories: Category[];
}

const props = defineProps<Props>();

// Form setup with existing category data
const form = useForm({
    name: props.category.name,
    slug: props.category.slug,
    description: props.category.description || '',
    parent_id: props.category.parent_id,
    is_active: props.category.is_active,
    sort_order: props.category.sort_order,
    meta_title: props.category.meta_title || '',
    meta_description: props.category.meta_description || '',
});

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

const submit = () => {
    form.put(route('admin.categories.update', props.category.id), {
        onSuccess: () => {
            // Handle success
        },
    });
};
</script>

<template>
    <Head :title="`Edit ${category.name} - Categories - Admin`" />

    <AdminLayout>
        <div class="container mx-auto max-w-4xl px-4 py-8">
            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                        <Edit class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">Edit Category</h1>
                        <p class="text-muted-foreground">Update category information</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('admin.categories.show', category.id)"
                        class="flex items-center space-x-2 text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        <span>Back to Category</span>
                    </Link>
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
                                            <SelectItem
                                                v-for="parentCategory in parentCategories"
                                                :key="parentCategory.id"
                                                :value="parentCategory.id"
                                            >
                                                {{ parentCategory.name }}
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
                            </CardContent>
                        </Card>

                        <!-- Actions -->
                        <Card>
                            <CardContent class="pt-6">
                                <Button type="submit" class="w-full" :disabled="form.processing">
                                    <Save class="mr-2 h-4 w-4" />
                                    Update Category
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
