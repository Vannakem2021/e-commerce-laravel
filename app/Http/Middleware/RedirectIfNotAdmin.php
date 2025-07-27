<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAdmin
{
    /**
     * Handle an incoming request.
     * 
     * Redirects non-admin users to appropriate pages instead of showing 403 errors
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If user is not authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }

        // If user is admin, allow access
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // If user is not admin, redirect to appropriate page
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Admin access required.',
                'redirect' => route('home')
            ], 403);
        }

        // For web requests, redirect to home with a message
        return redirect()->route('home')->with('error', 'You need admin privileges to access that page.');
    }
}
