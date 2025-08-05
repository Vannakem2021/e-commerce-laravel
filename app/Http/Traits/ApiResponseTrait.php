<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Return a successful JSON response
     */
    protected function successResponse(
        string $message = 'Operation successful',
        mixed $data = null,
        int $status = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response
     */
    protected function errorResponse(
        string $message = 'Operation failed',
        string $code = 'ERROR',
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'timestamp' => now()->toISOString(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a validation error response
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'The given data was invalid'
    ): JsonResponse {
        return $this->errorResponse(
            message: $message,
            code: 'VALIDATION_ERROR',
            errors: $errors,
            status: 422
        );
    }

    /**
     * Return a not found error response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse(
            message: $message,
            code: 'NOT_FOUND',
            status: 404
        );
    }

    /**
     * Return an unauthorized error response
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse(
            message: $message,
            code: 'UNAUTHORIZED',
            status: 401
        );
    }

    /**
     * Return a forbidden error response
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse(
            message: $message,
            code: 'FORBIDDEN',
            status: 403
        );
    }

    /**
     * Return a server error response
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse(
            message: $message,
            code: 'INTERNAL_ERROR',
            status: 500
        );
    }
}
