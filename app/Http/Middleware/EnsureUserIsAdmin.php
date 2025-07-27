<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * Ensure user has admin role - simplified middleware for admin-only areas
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        $authGuard = Auth::guard($guard);

        // Check if user is authenticated
        if (!$authGuard->check()) {
            return $this->handleUnauthenticated($request);
        }

        $user = $authGuard->user();

        // Check if user is admin
        if (!$user->hasRole('admin')) {
            // Log unauthorized admin access attempt
            logger()->warning('Unauthorized admin access attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_roles' => $user->getRoleNames()->toArray(),
                'route' => $request->route()?->getName(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->handleUnauthorized($request);
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
     * Handle unauthorized users
     */
    private function handleUnauthorized(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Admin access required.',
                'error' => 'admin_required'
            ], 403);
        }

        // For Inertia requests, return a proper error page
        if ($request->header('X-Inertia')) {
            return inertia('errors/403', [
                'message' => 'Admin access required to view this page.',
                'is_admin_required' => true
            ])->toResponse($request)->setStatusCode(403);
        }

        // For regular web requests
        abort(403, 'Admin access required.');
    }
}
