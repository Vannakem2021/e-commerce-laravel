<?php

namespace App\Services;

use App\Exceptions\CategoryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Service for handling category image operations.
 */
class CategoryImageService
{
    /**
     * Image storage disk.
     */
    protected string $disk = 'public';

    /**
     * Base path for category images.
     */
    protected string $basePath = 'categories';

    /**
     * Image size configurations.
     */
    protected array $sizes = [
        'thumbnail' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 400, 'height' => 400],
        'large' => ['width' => 800, 'height' => 800],
    ];

    /**
     * Allowed image MIME types.
     */
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Maximum file size in bytes (2MB).
     */
    protected int $maxFileSize = 2097152;

    /**
     * Upload and process category image.
     */
    public function uploadImage(UploadedFile $file, ?string $categorySlug = null): array
    {
        try {
            // Validate the uploaded file
            $this->validateImage($file);

            // Generate unique filename
            $filename = $this->generateFilename($file, $categorySlug);

            // Create directory structure
            $this->ensureDirectoryExists();

            // Process and save images in different sizes
            $imagePaths = $this->processAndSaveImages($file, $filename);

            Log::info('Category image uploaded successfully', [
                'filename' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'paths' => $imagePaths,
            ]);

            return $imagePaths;

        } catch (\Exception $e) {
            Log::error('Failed to upload category image', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);

            throw CategoryException::imageUploadError(
                'Failed to upload image: ' . $e->getMessage(),
                [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]
            );
        }
    }

    /**
     * Delete category image and all its variants.
     */
    public function deleteImage(string $imagePath): bool
    {
        try {
            $deleted = false;

            // Extract filename from path
            $filename = basename($imagePath);
            $nameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);

            // Delete all size variants
            foreach (array_keys($this->sizes) as $size) {
                $sizePath = "{$this->basePath}/{$size}/{$filename}";
                if (Storage::disk($this->disk)->exists($sizePath)) {
                    Storage::disk($this->disk)->delete($sizePath);
                    $deleted = true;
                }
            }

            // Delete original if it exists
            if (Storage::disk($this->disk)->exists($imagePath)) {
                Storage::disk($this->disk)->delete($imagePath);
                $deleted = true;
            }

            if ($deleted) {
                Log::info('Category image deleted successfully', [
                    'image_path' => $imagePath,
                    'filename' => $filename,
                ]);
            }

            return $deleted;

        } catch (\Exception $e) {
            Log::error('Failed to delete category image', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
            ]);

            return false;
        }
    }

    /**
     * Get image URL for a specific size.
     */
    public function getImageUrl(string $imagePath, string $size = 'medium'): ?string
    {
        if (!$imagePath) {
            return null;
        }

        $filename = basename($imagePath);
        $sizePath = "{$this->basePath}/{$size}/{$filename}";

        if (Storage::disk($this->disk)->exists($sizePath)) {
            return Storage::disk($this->disk)->url($sizePath);
        }

        // Fallback to original if size variant doesn't exist
        if (Storage::disk($this->disk)->exists($imagePath)) {
            return Storage::disk($this->disk)->url($imagePath);
        }

        return null;
    }

    /**
     * Get all image URLs for different sizes.
     */
    public function getAllImageUrls(string $imagePath): array
    {
        $urls = [];

        foreach (array_keys($this->sizes) as $size) {
            $urls[$size] = $this->getImageUrl($imagePath, $size);
        }

        return array_filter($urls);
    }

    /**
     * Validate uploaded image file.
     */
    protected function validateImage(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \InvalidArgumentException('Image file size exceeds maximum allowed size of 2MB.');
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException('Invalid image format. Only JPEG, PNG, GIF, and WebP are allowed.');
        }

        // Check if file is actually an image
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new \InvalidArgumentException('File is not a valid image.');
        }

        // Check image dimensions (minimum requirements)
        if ($imageInfo[0] < 100 || $imageInfo[1] < 100) {
            throw new \InvalidArgumentException('Image dimensions must be at least 100x100 pixels.');
        }

        // Check for potential security issues
        $this->validateImageSecurity($file);
    }

    /**
     * Validate image for security issues.
     */
    protected function validateImageSecurity(UploadedFile $file): void
    {
        // Read first few bytes to check for malicious content
        $handle = fopen($file->getPathname(), 'rb');
        $header = fread($handle, 1024);
        fclose($handle);

        // Check for PHP code in image files
        if (strpos($header, '<?php') !== false || strpos($header, '<?=') !== false) {
            throw new \InvalidArgumentException('Image file contains potentially malicious content.');
        }

        // Check for script tags
        if (strpos($header, '<script') !== false) {
            throw new \InvalidArgumentException('Image file contains potentially malicious content.');
        }
    }

    /**
     * Generate unique filename for the image.
     */
    protected function generateFilename(UploadedFile $file, ?string $categorySlug = null): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);

        if ($categorySlug) {
            return "{$categorySlug}_{$timestamp}_{$random}.{$extension}";
        }

        return "category_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Ensure directory structure exists.
     */
    protected function ensureDirectoryExists(): void
    {
        foreach (array_keys($this->sizes) as $size) {
            $directory = "{$this->basePath}/{$size}";
            if (!Storage::disk($this->disk)->exists($directory)) {
                Storage::disk($this->disk)->makeDirectory($directory);
            }
        }
    }

    /**
     * Process and save images in different sizes.
     */
    protected function processAndSaveImages(UploadedFile $file, string $filename): array
    {
        $imagePaths = [];

        // Save original image
        $originalPath = "{$this->basePath}/{$filename}";
        Storage::disk($this->disk)->putFileAs($this->basePath, $file, $filename);
        $imagePaths['original'] = $originalPath;

        // Create and save resized versions
        foreach ($this->sizes as $sizeName => $dimensions) {
            $resizedPath = $this->createResizedImage($file, $filename, $sizeName, $dimensions);
            $imagePaths[$sizeName] = $resizedPath;
        }

        return $imagePaths;
    }

    /**
     * Create resized image variant.
     */
    protected function createResizedImage(UploadedFile $file, string $filename, string $sizeName, array $dimensions): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getPathname());

        // Resize image maintaining aspect ratio
        $image->cover($dimensions['width'], $dimensions['height']);

        // Save in original format
        $originalSizePath = "{$this->basePath}/{$sizeName}/{$filename}";
        $originalContent = $image->encode();
        Storage::disk($this->disk)->put($originalSizePath, $originalContent);

        // Try to save as WebP for better compression (if supported)
        try {
            $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
            $sizePath = "{$this->basePath}/{$sizeName}/{$webpFilename}";
            $webpContent = $image->toWebp(85);
            Storage::disk($this->disk)->put($sizePath, $webpContent);
            return $sizePath;
        } catch (\Exception $e) {
            // Fallback to original format if WebP conversion fails
            Log::warning('WebP conversion failed, using original format', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);
            return $originalSizePath;
        }
    }
}
