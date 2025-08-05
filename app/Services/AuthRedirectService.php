<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

class AuthRedirectService
{
    /**
     * Get the appropriate redirect response after login based on user role
     */
    public function getLoginRedirect(User $user, ?string $intended = null): RedirectResponse
    {
        // Admin users always go to admin dashboard regardless of intended destination
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Regular users go to intended destination or home page
        if ($user->hasRole('user')) {
            // If there's an intended URL and it's safe, redirect there
            if ($intended && $this->isSafeRedirectUrl($intended)) {
                return redirect()->to($intended);
            }
            
            // Otherwise redirect to home page
            return redirect()->route('home');
        }

        // Fallback for users without roles (shouldn't happen in normal flow)
        return redirect()->route('home');
    }

    /**
     * Get the appropriate redirect response after registration based on user role
     */
    public function getRegistrationRedirect(User $user): RedirectResponse
    {
        // Admin users go to admin dashboard
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        // Regular users go to home page
        if ($user->hasRole('user')) {
            return redirect()->route('home');
        }

        // Fallback for users without roles
        return redirect()->route('home');
    }

    /**
     * Get the intended URL from the session
     */
    public function getIntendedUrl(): ?string
    {
        return session()->pull('url.intended');
    }

    /**
     * Check if a URL is safe for redirect
     * Prevents open redirect vulnerabilities
     */
    private function isSafeRedirectUrl(string $url): bool
    {
        // Check if URL is relative or belongs to our domain
        if (str_starts_with($url, '/')) {
            return true;
        }

        // Check if URL belongs to our application domain
        $appUrl = parse_url(config('app.url'));
        $redirectUrl = parse_url($url);

        if (!$redirectUrl || !isset($redirectUrl['host'])) {
            return false;
        }

        // Allow same domain redirects
        return $redirectUrl['host'] === ($appUrl['host'] ?? null);
    }

    /**
     * Get default redirect URL for a specific role
     */
    public function getDefaultRedirectForRole(string $role): string
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'user' => route('home'),
            default => route('home'),
        };
    }

    /**
     * Check if a user should be redirected to admin area
     */
    public function shouldRedirectToAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Get redirect URL with fallback logic
     */
    public function getRedirectWithFallback(User $user, ?string $intended = null, ?string $fallback = null): RedirectResponse
    {
        // Admin users always go to admin dashboard
        if ($this->shouldRedirectToAdmin($user)) {
            return redirect()->route('admin.dashboard');
        }

        // Try intended URL first
        if ($intended && $this->isSafeRedirectUrl($intended)) {
            return redirect()->to($intended);
        }

        // Try fallback URL
        if ($fallback && $this->isSafeRedirectUrl($fallback)) {
            return redirect()->to($fallback);
        }

        // Default to role-based redirect
        return redirect()->to($this->getDefaultRedirectForRole($user->getRoleNames()->first() ?? 'user'));
    }
}
