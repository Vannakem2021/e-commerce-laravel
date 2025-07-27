<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     *
     * Enhanced permission middleware with better error handling and logging
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $guard = null): Response
    {
        $authGuard = Auth::guard($guard);

        // Check if user is authenticated
        if (!$authGuard->check()) {
            return $this->handleUnauthenticated($request);
        }

        $user = $authGuard->user();

        // Check if user has the required permission
        if (!$user->can($permission)) {
            // Log unauthorized access attempt
            logger()->warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'permission' => $permission,
                'route' => $request->route()?->getName(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->handleUnauthorized($request, $permission);
        }

        return $next($request);
    }

    /**
     * Handle unauthenticated users
     */
    private function handleUnauthenticated(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'authentication_required'
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Handle unauthorized users (authenticated but lacking permission)
     */
    private function handleUnauthorized(Request $request, string $permission): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You do not have permission to access this resource.',
                'error' => 'insufficient_permissions',
                'required_permission' => $permission
            ], 403);
        }

        // For Inertia requests, return a proper error page
        if ($request->header('X-Inertia')) {
            return inertia('errors/403', [
                'message' => 'You do not have permission to access this resource.',
                'required_permission' => $permission
            ])->toResponse($request)->setStatusCode(403);
        }

        // For regular web requests
        abort(403, 'You do not have permission to access this resource.');
    }
}
