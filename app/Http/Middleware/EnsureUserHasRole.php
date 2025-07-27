<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Enhanced role middleware with support for multiple roles and better error handling
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles, ?string $guard = null): Response
    {
        $authGuard = Auth::guard($guard);

        // Check if user is authenticated
        if (!$authGuard->check()) {
            return $this->handleUnauthenticated($request);
        }

        $user = $authGuard->user();
        $requiredRoles = $this->parseRoles($roles);

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($requiredRoles)) {
            // Log unauthorized access attempt
            logger()->warning('Unauthorized role access attempt', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_roles' => $user->getRoleNames()->toArray(),
                'required_roles' => $requiredRoles,
                'route' => $request->route()?->getName(),
                'url' => $request->url(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->handleUnauthorized($request, $requiredRoles);
        }

        return $next($request);
    }

    /**
     * Parse roles string into array
     */
    private function parseRoles(string $roles): array
    {
        return array_map('trim', explode('|', $roles));
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
     * Handle unauthorized users (authenticated but lacking role)
     */
    private function handleUnauthorized(Request $request, array $requiredRoles): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You do not have the required role to access this resource.',
                'error' => 'insufficient_role',
                'required_roles' => $requiredRoles
            ], 403);
        }

        // For Inertia requests, return a proper error page
        if ($request->header('X-Inertia')) {
            return inertia('errors/403', [
                'message' => 'You do not have the required role to access this resource.',
                'required_roles' => $requiredRoles
            ])->toResponse($request)->setStatusCode(403);
        }

        // For regular web requests
        abort(403, 'You do not have the required role to access this resource.');
    }
}
