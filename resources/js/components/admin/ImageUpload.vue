<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Image as ImageIcon, Star, Upload, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface ProductImage {
    id?: number;
    image_path: string;
    alt_text?: string;
    is_primary: boolean;
    sort_order: number;
    file?: File;
    preview?: string;
    name?: string;
    size?: number;
}

interface Props {
    modelValue: ProductImage[];
    maxFiles?: number;
    maxFileSize?: number; // in MB
    acceptedTypes?: string[];
}

interface Emits {
    (e: 'update:modelValue', value: ProductImage[]): void;
}

const props = withDefaults(defineProps<Props>(), {
    maxFiles: 10,
    maxFileSize: 5,
    acceptedTypes: () => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
});

const emit = defineEmits<Emits>();

// Reactive state
const isDragOver = ref(false);
const isUploading = ref(false);
const fileInput = ref<HTMLInputElement>();
// Computed
const images = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const canAddMore = computed(() => images.value.length < props.maxFiles);

// Methods
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        handleFiles(Array.from(target.files));
    }
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;

    if (event.dataTransfer?.files) {
        handleFiles(Array.from(event.dataTransfer.files));
    }
};

const handleFiles = async (files: File[]) => {
    if (!canAddMore.value) {
        alert(`Maximum ${props.maxFiles} images allowed`);
        return;
    }

    const validFiles = files.filter((file) => {
        // Check file type
        if (!props.acceptedTypes.includes(file.type)) {
            alert(`File ${file.name} is not a supported image type`);
            return false;
        }

        // Check file size
        if (file.size > props.maxFileSize * 1024 * 1024) {
            alert(`File ${file.name} is too large. Maximum size is ${props.maxFileSize}MB`);
            return false;
        }

        return true;
    });

    if (validFiles.length === 0) return;

    isUploading.value = true;

    try {
        const newImages: ProductImage[] = [];

        for (const file of validFiles) {
            const preview = await createPreview(file);
            const isFirstImage = images.value.length === 0 && newImages.length === 0;

            newImages.push({
                image_path: preview,
                alt_text: '',
                is_primary: isFirstImage,
                sort_order: images.value.length + newImages.length,
                file,
                preview,
                name: file.name,
                size: file.size,
            });
        }

        images.value = [...images.value, ...newImages];
    } catch (error) {
        console.error('Error processing files:', error);
        alert('Error processing files. Please try again.');
    } finally {
        isUploading.value = false;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
};

const createPreview = (file: File): Promise<string> => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target?.result as string);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
};

const removeImage = (index: number) => {
    const imageToRemove = images.value[index];
    const newImages = images.value.filter((_, i) => i !== index);

    // If we removed the primary image, make the first remaining image primary
    if (imageToRemove.is_primary && newImages.length > 0) {
        newImages[0].is_primary = true;
    }

    // Update sort orders
    newImages.forEach((img, i) => {
        img.sort_order = i;
    });

    images.value = newImages;
};

const setPrimary = (index: number) => {
    images.value = images.value.map((img, i) => ({
        ...img,
        is_primary: i === index,
    }));
};

const clearAllImages = () => {
    images.value = [];
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const updateAltText = (index: number, altText: string) => {
    images.value = images.value.map((img, i) => (i === index ? { ...img, alt_text: altText } : img));
};

const moveImage = (fromIndex: number, toIndex: number) => {
    const newImages = [...images.value];
    const [movedImage] = newImages.splice(fromIndex, 1);
    newImages.splice(toIndex, 0, movedImage);

    // Update sort orders
    newImages.forEach((img, i) => {
        img.sort_order = i;
    });

    images.value = newImages;
};

const openFileDialog = () => {
    fileInput.value?.click();
};

// Drag and drop handlers
const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = true;
};

const handleDragLeave = () => {
    isDragOver.value = false;
};
</script>

<template>
    <div class="space-y-6">
        <!-- Clean Upload Section -->
        <div class="space-y-4">
            <!-- Header -->
            <div class="flex items-center space-x-2">
                <Upload class="h-5 w-5 text-primary" />
                <h3 class="text-lg font-semibold text-foreground">Image Upload</h3>
            </div>

            <!-- Description -->
            <p class="text-sm text-muted-foreground">Drag and drop your images here or click to browse</p>

            <!-- Upload Area -->
            <div
                :class="[
                    'relative rounded-lg border-2 border-dashed p-8 text-center transition-all duration-200',
                    isDragOver ? 'border-primary bg-primary/5' : 'border-muted-foreground/30 hover:border-primary/50 hover:bg-muted/20',
                    canAddMore ? 'cursor-pointer' : 'cursor-not-allowed opacity-50',
                ]"
                @drop="handleDrop"
                @dragover="handleDragOver"
                @dragleave="handleDragLeave"
                @click="canAddMore ? openFileDialog : null"
            >
                <div class="space-y-4">
                    <!-- Icon -->
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-lg bg-primary/10">
                        <ImageIcon class="h-8 w-8 text-primary" />
                    </div>

                    <!-- Text -->
                    <div class="space-y-2">
                        <h4 class="text-base font-medium text-foreground">Drag & Drop Images Here</h4>
                        <p class="text-sm text-muted-foreground">or</p>
                        <Button type="button" variant="default" size="sm" @click.stop="openFileDialog" :disabled="!canAddMore">
                            <Upload class="mr-2 h-4 w-4" />
                            Browse Files
                        </Button>
                    </div>

                    <!-- File Info -->
                    <p class="text-xs text-muted-foreground">Supported formats: JPG, PNG, GIF, WebP (Max {{ maxFileSize }}MB per file)</p>
                </div>
            </div>
        </div>

        <!-- Enhanced Loading State -->
        <div v-if="isUploading" class="flex items-center justify-center rounded-lg bg-primary/5 py-6">
            <div class="flex items-center space-x-3">
                <div class="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
                <span class="text-base font-medium text-primary">Processing images...</span>
            </div>
        </div>
    </div>

    <!-- Hidden File Input -->
    <input ref="fileInput" type="file" multiple :accept="acceptedTypes.join(',')" class="hidden" @change="handleFileSelect" />

    <!-- Redesigned Clean Image Grid -->
    <div v-if="images.length > 0" class="space-y-6">
        <!-- Clean Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                    <ImageIcon class="h-4 w-4 text-primary" />
                </div>
                <h3 class="text-base font-semibold text-foreground">Preview ({{ images.length }})</h3>
            </div>
            <Button
                type="button"
                @click="clearAllImages"
                variant="outline"
                size="sm"
                class="text-destructive hover:bg-destructive hover:text-destructive-foreground"
            >
                Clear All
            </Button>
        </div>

        <!-- Clean Image Grid -->
        <div class="grid grid-cols-3 gap-4">
            <div v-for="(image, index) in images" :key="index" class="group relative">
                <!-- Image Container with Fixed Aspect Ratio -->
                <div class="relative aspect-[4/3] overflow-hidden rounded-lg border border-border bg-muted">
                    <img
                        :src="image.preview || image.image_path"
                        :alt="image.alt_text || `Product image ${index + 1}`"
                        class="h-full w-full object-cover"
                    />

                    <!-- Remove Button -->
                    <Button
                        type="button"
                        @click="removeImage(index)"
                        size="sm"
                        variant="destructive"
                        class="absolute top-2 right-2 h-6 w-6 rounded-full p-0 opacity-0 transition-opacity group-hover:opacity-100"
                    >
                        <X class="h-3 w-3" />
                    </Button>

                    <!-- Primary Badge -->
                    <div
                        v-if="image.is_primary"
                        class="absolute top-2 left-2 rounded-full bg-primary px-2 py-1 text-xs font-medium text-primary-foreground"
                    >
                        Primary
                    </div>
                </div>

                <!-- Image Info -->
                <div class="mt-2 space-y-2">
                    <!-- Filename and Size -->
                    <div class="text-center">
                        <p class="truncate text-sm font-medium text-foreground">{{ image.name || `Image ${index + 1}` }}</p>
                        <p class="text-xs text-muted-foreground">{{ formatFileSize(image.size || 0) }}</p>
                    </div>

                    <!-- Set Primary Button -->
                    <Button type="button" v-if="!image.is_primary" @click="setPrimary(index)" variant="outline" size="sm" class="w-full">
                        <Star class="mr-1 h-3 w-3" />
                        Set Primary
                    </Button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Empty State -->
    <Card v-if="images.length === 0 && !canAddMore" class="border-dashed">
        <CardContent class="flex flex-col items-center justify-center py-12">
            <div class="mb-4 rounded-full bg-muted/50 p-4">
                <ImageIcon class="h-8 w-8 text-muted-foreground" />
            </div>
            <h4 class="mb-2 text-base font-medium text-foreground">No images uploaded</h4>
            <p class="text-sm text-muted-foreground">Maximum {{ maxFiles }} images reached</p>
        </CardContent>
    </Card>
</template>
