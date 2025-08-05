<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Brand, PaginatedResponse } from '@/types/product';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, ExternalLink, Eye, Image, MoreHorizontal, Plus, Search, Tag, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    brands: PaginatedResponse<Brand>;
    filters?: {
        search?: string;
        status?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    filters: () => ({}),
});

// Reactive filters
const searchQuery = ref(props.filters?.search || '');

// Methods
const applyFilters = () => {
    const filters: Record<string, any> = {};

    if (searchQuery.value) filters.search = searchQuery.value;

    router.get(route('admin.brands.index'), filters, {
        preserveState: true,
        replace: true,
    });
};

const deleteBrand = (brand: Brand) => {
    if (confirm(`Are you sure you want to delete "${brand.name}"?`)) {
        router.delete(route('admin.brands.destroy', brand.id), {
            onSuccess: () => {
                console.log('Brand deleted successfully');
            },
            onError: (errors) => {
                console.error('Deletion failed:', errors);
                alert('Failed to delete brand. Check console for details.');
            },
            onFinish: () => {
                console.log('Deletion request completed');
            },
        });
    }
};

const getStatusBadgeVariant = (isActive: boolean) => {
    return isActive ? 'default' : 'secondary';
};
</script>

<template>
    <Head title="Brands - Admin" />

    <AdminLayout>
        <div class="container mx-auto max-w-7xl px-4 py-8">
            <!-- Page Header -->
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                        <Tag class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">Brands</h1>
                        <p class="text-muted-foreground">Manage product brands</p>
                    </div>
                </div>
                <Link :href="route('admin.brands.create')">
                    <Button class="flex items-center space-x-2">
                        <Plus class="h-4 w-4" />
                        <span>Add Brand</span>
                    </Button>
                </Link>
            </div>

            <!-- Search -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <Search class="h-4 w-4" />
                        <span>Search Brands</span>
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center space-x-4">
                        <div class="relative flex-1">
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                            <Input v-model="searchQuery" placeholder="Search brands by name..." class="pl-10" @keyup.enter="applyFilters" />
                        </div>
                        <Button @click="applyFilters" size="sm"> Search </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Brands Table -->
            <Card>
                <CardHeader>
                    <CardTitle> Brands ({{ brands.total }}) </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-16"></TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Slug</TableHead>
                                    <TableHead>Website</TableHead>
                                    <TableHead>Products</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Sort Order</TableHead>
                                    <TableHead class="w-16"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <!-- Empty State -->
                                <TableRow v-if="brands.data.length === 0">
                                    <TableCell colspan="8" class="py-8 text-center">
                                        <div class="flex flex-col items-center space-y-2">
                                            <Tag class="h-8 w-8 text-muted-foreground" />
                                            <p class="text-muted-foreground">No brands found</p>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <!-- Brand Rows -->
                                <TableRow v-for="brand in brands.data" :key="brand.id" v-else>
                                    <!-- Brand Logo -->
                                    <TableCell>
                                        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg bg-muted">
                                            <Image v-if="!brand.logo" class="h-6 w-6 text-muted-foreground" />
                                            <img v-else :src="brand.logo" :alt="brand.name" class="h-12 w-12 rounded-lg object-cover" />
                                        </div>
                                    </TableCell>

                                    <!-- Brand Name -->
                                    <TableCell>
                                        <div>
                                            <div class="font-medium">{{ brand.name }}</div>
                                            <div v-if="brand.description" class="line-clamp-1 text-sm text-muted-foreground">
                                                {{ brand.description }}
                                            </div>
                                        </div>
                                    </TableCell>

                                    <!-- Slug -->
                                    <TableCell>
                                        <code class="rounded bg-muted px-2 py-1 text-sm">
                                            {{ brand.slug }}
                                        </code>
                                    </TableCell>

                                    <!-- Website -->
                                    <TableCell>
                                        <a
                                            v-if="brand.website"
                                            :href="brand.website"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center text-primary hover:underline"
                                        >
                                            <span class="text-sm">Visit</span>
                                            <ExternalLink class="ml-1 h-3 w-3" />
                                        </a>
                                        <span v-else class="text-sm text-muted-foreground">N/A</span>
                                    </TableCell>

                                    <!-- Products Count -->
                                    <TableCell>
                                        <span class="font-medium">{{ brand.products?.length || 0 }}</span>
                                    </TableCell>

                                    <!-- Status -->
                                    <TableCell>
                                        <Badge :variant="getStatusBadgeVariant(brand.is_active)">
                                            {{ brand.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>

                                    <!-- Sort Order -->
                                    <TableCell>
                                        <span class="text-sm">{{ brand.sort_order }}</span>
                                    </TableCell>

                                    <!-- Actions -->
                                    <TableCell>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                    <span class="sr-only">Open menu</span>
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('admin.brands.show', brand.id)" class="flex items-center">
                                                        <Eye class="mr-2 h-4 w-4" />
                                                        View
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('admin.brands.edit', brand.id)" class="flex items-center">
                                                        <Edit class="mr-2 h-4 w-4" />
                                                        Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="deleteBrand(brand)" class="text-destructive focus:text-destructive">
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="brands.last_page > 1" class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">Showing {{ brands.from }} to {{ brands.to }} of {{ brands.total }} results</div>
                        <div class="flex items-center space-x-2">
                            <Button
                                v-for="link in brands.links"
                                :key="link.label"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                size="sm"
                                @click="link.url && router.get(link.url)"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
