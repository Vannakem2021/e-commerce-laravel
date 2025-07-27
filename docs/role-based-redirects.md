# Role-Based Authentication Redirects

This document explains the role-based redirect system implemented in the Laravel Vue E-commerce application.

## Overview

The application implements a simplified 2-role system with intelligent redirect logic that provides different user experiences based on user roles:

- **Admin users**: Always redirected to `/dashboard` for administrative tasks
- **Regular users**: Redirected to intended pages or home page for shopping experience

## Roles

### Admin Role
- **Redirect after login**: Always `/dashboard` (ignores intended URL)
- **Redirect after registration**: `/dashboard`
- **Purpose**: Administrative interface access
- **Permissions**: All permissions (full system access)

### User Role  
- **Redirect after login**: Intended URL or `/` (home page)
- **Redirect after registration**: `/` (home page)
- **Purpose**: Shopping and customer account management
- **Permissions**: Limited (view dashboard, edit profile, create/view orders)

## Implementation

### AuthRedirectService

The `App\Services\AuthRedirectService` class handles all redirect logic:

```php
// Login redirects
$redirectService->getLoginRedirect($user, $intendedUrl);

// Registration redirects  
$redirectService->getRegistrationRedirect($user);

// Check if URL is safe for redirect
$redirectService->isSafeRedirectUrl($url);
```

### Modified Controllers

#### AuthenticatedSessionController
- **Before**: All users → `/dashboard`
- **After**: 
  - Admin users → `/dashboard`
  - Regular users → intended URL or `/`

#### RegisteredUserController
- **Before**: All users → `/dashboard`
- **After**:
  - Admin users → `/dashboard` 
  - Regular users → `/`

#### ConfirmablePasswordController
- **Before**: All users → `/dashboard`
- **After**: Same logic as login (role-based)

## Security Features

### Safe URL Validation
- Prevents open redirect vulnerabilities
- Only allows relative URLs or same-domain URLs
- Malformed URLs fallback to safe defaults

### Intended URL Handling
- Preserves user's original destination
- Admin users bypass intended URL for security
- Regular users get seamless experience

## Usage Examples

### Login Flow
```php
// Admin user login
POST /login → /dashboard (always)

// Regular user login  
POST /login → /settings/profile (if that was intended)
POST /login → / (if no intended URL)
```

### Registration Flow
```php
// Admin registration (manual role assignment)
POST /register → /dashboard

// Regular user registration (default)
POST /register → /
```

### Middleware Integration
```php
// Admin-only routes
Route::middleware(['auth', 'admin'])->group(function () {
    // Admin routes
});

// Permission-based routes
Route::middleware(['auth', 'ensure.permission:manage-products'])->group(function () {
    // Product management
});
```

## Testing

Comprehensive tests cover:
- Role-based login redirects
- Role-based registration redirects  
- Intended URL preservation
- Safe URL validation
- Admin bypass behavior
- Fallback scenarios

Run tests:
```bash
php artisan test --filter=AuthRedirectTest
```

## Configuration

### Default Redirects
- Admin: `/dashboard`
- User: `/` (home page)
- Unknown role: `/` (fallback)

### Customization
Modify `AuthRedirectService::getDefaultRedirectForRole()` to change default destinations.

## Benefits

1. **Better UX**: Users land on appropriate pages for their role
2. **Security**: Admins always go to secure admin area
3. **Flexibility**: Regular users can continue their shopping journey
4. **Safety**: Prevents redirect vulnerabilities
5. **Maintainable**: Centralized redirect logic

## Migration Notes

### Breaking Changes
- Login no longer redirects all users to dashboard
- Registration redirects to home instead of dashboard for regular users

### Backward Compatibility
- Admin users still get dashboard access
- Intended URL functionality preserved
- All security features maintained

## Future Enhancements

Potential improvements:
- Role-specific landing pages
- Onboarding flows for new users
- Dynamic redirects based on user preferences
- Integration with shopping cart state
