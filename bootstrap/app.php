<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            // Spatie Permission Middleware
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // Custom Enhanced Middleware
            'ensure.permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
            'ensure.role' => \App\Http\Middleware\EnsureUserHasRole::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'redirect.admin' => \App\Http\Middleware\RedirectIfNotAdmin::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Global exception handler for consistent error responses
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Only handle explicit API requests (routes starting with /api/ or explicit JSON requests)
            $isApiRequest = str_starts_with($request->path(), 'api/') ||
                           ($request->expectsJson() && !$request->header('X-Inertia'));

            if (!$isApiRequest) {
                return null; // Let Laravel handle normally
            }

            $status = 500;
            $message = 'An unexpected error occurred';
            $code = 'INTERNAL_ERROR';

            // Handle specific exception types
            if ($e instanceof ValidationException) {
                $status = 422;
                $message = 'The given data was invalid';
                $code = 'VALIDATION_ERROR';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => $e->errors(),
                    'code' => $code,
                    'timestamp' => now()->toISOString(),
                ], $status);
            }

            if ($e instanceof HttpException) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() ?: 'HTTP Error';
                $code = 'HTTP_' . $status;
            }

            // For other exceptions, use the message if it's safe to expose
            if (!app()->isProduction() || in_array($status, [400, 401, 403, 404, 409, 422, 429])) {
                $message = $e->getMessage() ?: $message;
            }

            $response = [
                'success' => false,
                'message' => $message,
                'code' => $code,
                'timestamp' => now()->toISOString(),
            ];

            // Add debug info in non-production environments
            if (!app()->isProduction()) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ];
            }

            return response()->json($response, $status);
        });
    })->create();

