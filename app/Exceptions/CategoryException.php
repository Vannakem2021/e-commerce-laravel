<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Custom exception class for category-related errors.
 */
class CategoryException extends Exception
{
    /**
     * The error type for categorization.
     */
    protected string $errorType;

    /**
     * Additional context data for logging.
     */
    protected array $context;

    /**
     * HTTP status code for the error.
     */
    protected int $statusCode;

    /**
     * Create a new CategoryException instance.
     */
    public function __construct(
        string $message = 'Category operation failed',
        string $errorType = 'general',
        int $statusCode = 500,
        array $context = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->errorType = $errorType;
        $this->context = $context;
        $this->statusCode = $statusCode;
    }

    /**
     * Create a validation error exception.
     */
    public static function validationError(string $message, array $context = []): self
    {
        return new self($message, 'validation', 422, $context);
    }

    /**
     * Create a not found error exception.
     */
    public static function notFound(string $message = 'Category not found', array $context = []): self
    {
        return new self($message, 'not_found', 404, $context);
    }

    /**
     * Create a deletion constraint error exception.
     */
    public static function deletionConstraint(string $message, array $context = []): self
    {
        return new self($message, 'deletion_constraint', 400, $context);
    }

    /**
     * Create a circular reference error exception.
     */
    public static function circularReference(string $message = 'Circular reference detected in category hierarchy', array $context = []): self
    {
        return new self($message, 'circular_reference', 400, $context);
    }

    /**
     * Create an image upload error exception.
     */
    public static function imageUploadError(string $message, array $context = []): self
    {
        return new self($message, 'image_upload', 422, $context);
    }

    /**
     * Create a cache operation error exception.
     */
    public static function cacheError(string $message, array $context = []): self
    {
        return new self($message, 'cache_error', 500, $context);
    }

    /**
     * Create a database operation error exception.
     */
    public static function databaseError(string $message, array $context = []): self
    {
        return new self($message, 'database_error', 500, $context);
    }

    /**
     * Report the exception for logging.
     */
    public function report(): void
    {
        $logContext = array_merge($this->context, [
            'error_type' => $this->errorType,
            'status_code' => $this->statusCode,
            'exception_class' => get_class($this),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
        ]);

        // Log based on severity
        if ($this->statusCode >= 500) {
            Log::error("Category Exception: {$this->getMessage()}", $logContext);
        } elseif ($this->statusCode >= 400) {
            Log::warning("Category Exception: {$this->getMessage()}", $logContext);
        } else {
            Log::info("Category Exception: {$this->getMessage()}", $logContext);
        }
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        // Log the exception
        $this->report();

        // Determine response format based on request type
        // For Inertia requests, we want to redirect, not return JSON
        if (($request->expectsJson() || $request->is('api/*')) && !$request->header('X-Inertia')) {
            return $this->renderJsonResponse();
        }

        return $this->renderRedirectResponse($request);
    }

    /**
     * Render JSON response for API requests.
     */
    protected function renderJsonResponse(): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'type' => $this->errorType,
                'message' => $this->getMessage(),
                'code' => $this->statusCode,
            ],
        ];

        // Add context in development environment
        if (config('app.debug')) {
            $response['error']['context'] = $this->context;
            $response['error']['file'] = $this->getFile();
            $response['error']['line'] = $this->getLine();
        }

        return response()->json($response, $this->statusCode);
    }

    /**
     * Render redirect response for web requests.
     */
    protected function renderRedirectResponse(Request $request): RedirectResponse
    {
        $redirectUrl = $this->getRedirectUrl($request);

        if ($this->statusCode >= 500) {
            return redirect($redirectUrl)->with('error', 'An unexpected error occurred. Please try again.');
        }

        return redirect($redirectUrl)->with('error', $this->getMessage());
    }

    /**
     * Get the appropriate redirect URL based on the request.
     */
    protected function getRedirectUrl(Request $request): string
    {
        // Try to redirect back, fallback to categories index
        if ($request->headers->get('referer')) {
            return back()->getTargetUrl();
        }

        return route('admin.categories.index');
    }

    /**
     * Get the error type.
     */
    public function getErrorType(): string
    {
        return $this->errorType;
    }

    /**
     * Get the context data.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
