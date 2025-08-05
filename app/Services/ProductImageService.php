<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductImageService
{
    protected ImageManager $imageManager;
    
    protected array $imageSizes = [
        'thumbnail' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 400, 'height' => 400],
        'large' => ['width' => 800, 'height' => 800],
        'original' => null, // Keep original size
    ];

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Upload and process product images
     */
    public function uploadImages(Product $product, array $files, array $imageData = []): array
    {
        $uploadedImages = [];

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $imageRecord = $this->processAndStoreImage($product, $file, $imageData[$index] ?? []);
                if ($imageRecord) {
                    $uploadedImages[] = $imageRecord;
                }
            }
        }

        return $uploadedImages;
    }

    /**
     * Process and store a single image
     */
    protected function processAndStoreImage(Product $product, UploadedFile $file, array $metadata = []): ?ProductImage
    {
        // Validate file
        if (!$this->validateImage($file)) {
            return null;
        }

        // Generate unique filename
        $filename = $this->generateFilename($file);
        $productPath = "products/{$product->id}";

        try {
            // Create directory structure
            $this->ensureDirectoryExists($productPath);

            // Process and store different sizes
            $imagePaths = $this->processImageSizes($file, $productPath, $filename);

            // Create database record
            $imageRecord = ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $imagePaths['original'],
                'alt_text' => $metadata['alt_text'] ?? $product->name,
                'is_primary' => $metadata['is_primary'] ?? false,
                'sort_order' => $metadata['sort_order'] ?? 0,
                'image_data' => [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'sizes' => $imagePaths,
                ],
            ]);

            return $imageRecord;

        } catch (\Exception $e) {
            // Clean up any uploaded files on error
            $this->cleanupImageFiles($productPath, $filename);
            throw $e;
        }
    }

    /**
     * Process image into different sizes
     */
    protected function processImageSizes(UploadedFile $file, string $productPath, string $filename): array
    {
        $imagePaths = [];
        $image = $this->imageManager->read($file->getPathname());

        foreach ($this->imageSizes as $sizeName => $dimensions) {
            $sizeFilename = $this->getSizeFilename($filename, $sizeName);
            $sizePath = "{$productPath}/{$sizeName}/{$sizeFilename}";

            if ($dimensions) {
                // Resize image maintaining aspect ratio
                $resizedImage = $image->scale(
                    width: $dimensions['width'],
                    height: $dimensions['height']
                );
                
                // Store resized image
                Storage::disk('public')->put($sizePath, $resizedImage->encode());
            } else {
                // Store original image
                Storage::disk('public')->put($sizePath, file_get_contents($file->getPathname()));
            }

            $imagePaths[$sizeName] = $sizePath;
        }

        return $imagePaths;
    }

    /**
     * Validate uploaded image
     */
    protected function validateImage(UploadedFile $file): bool
    {
        // Check file type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return false;
        }

        // Check file size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            return false;
        }

        // Check image dimensions
        $imageInfo = getimagesize($file->getPathname());
        if (!$imageInfo || $imageInfo[0] < 100 || $imageInfo[1] < 100) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $unique = Str::random(8);
        
        return "{$name}-{$unique}.{$extension}";
    }

    /**
     * Get filename for specific size
     */
    protected function getSizeFilename(string $originalFilename, string $size): string
    {
        if ($size === 'original') {
            return $originalFilename;
        }

        $pathInfo = pathinfo($originalFilename);
        return "{$pathInfo['filename']}-{$size}.{$pathInfo['extension']}";
    }

    /**
     * Ensure directory exists
     */
    protected function ensureDirectoryExists(string $path): void
    {
        foreach (array_keys($this->imageSizes) as $size) {
            Storage::disk('public')->makeDirectory("{$path}/{$size}");
        }
    }

    /**
     * Clean up image files
     */
    protected function cleanupImageFiles(string $productPath, string $filename): void
    {
        foreach (array_keys($this->imageSizes) as $size) {
            $sizeFilename = $this->getSizeFilename($filename, $size);
            Storage::disk('public')->delete("{$productPath}/{$size}/{$sizeFilename}");
        }
    }

    /**
     * Delete product image and all its sizes
     */
    public function deleteImage(ProductImage $image): bool
    {
        try {
            // Delete all size variants
            if ($image->image_data && isset($image->image_data['sizes'])) {
                foreach ($image->image_data['sizes'] as $sizePath) {
                    Storage::disk('public')->delete($sizePath);
                }
            } else {
                // Fallback: delete main image path
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete database record
            $image->delete();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reorder product images
     */
    public function reorderImages(Product $product, array $imageIds): void
    {
        foreach ($imageIds as $index => $imageId) {
            ProductImage::where('product_id', $product->id)
                ->where('id', $imageId)
                ->update(['sort_order' => $index]);
        }
    }

    /**
     * Set primary image
     */
    public function setPrimaryImage(Product $product, int $imageId): void
    {
        // Remove primary flag from all images
        ProductImage::where('product_id', $product->id)
            ->update(['is_primary' => false]);

        // Set new primary image
        ProductImage::where('product_id', $product->id)
            ->where('id', $imageId)
            ->update(['is_primary' => true]);
    }

    /**
     * Get image URL for specific size
     */
    public function getImageUrl(ProductImage $image, string $size = 'medium'): string
    {
        if ($image->image_data && isset($image->image_data['sizes'][$size])) {
            return Storage::disk('public')->url($image->image_data['sizes'][$size]);
        }

        // Fallback to main image path
        return Storage::disk('public')->url($image->image_path);
    }
}
