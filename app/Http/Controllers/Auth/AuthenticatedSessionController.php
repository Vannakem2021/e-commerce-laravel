<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthRedirectService;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private AuthRedirectService $redirectService,
        private CartService $cartService
    ) {}

    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     * Implements role-based redirects after successful login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Store session ID before authentication for guest cart transfer
        $sessionId = Session::getId();

        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Transfer guest cart to authenticated user
        try {
            $this->cartService->transferGuestCart($sessionId, $user->id);
        } catch (\Exception $e) {
            // Log error but don't fail login
            Log::warning('Failed to transfer guest cart during login', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
        }

        $intendedUrl = $this->redirectService->getIntendedUrl();

        // Get role-based redirect
        return $this->redirectService->getLoginRedirect($user, $intendedUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
