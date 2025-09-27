<?php

namespace App\Traits;

trait SecurityHelpers
{
    /**
     * Verify admin access with extra security checks
     */
    protected function verifyAdminAccess()
    {
        if (!auth()->check()) {
            abort(401, 'Authentication required');
        }

        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            // Log security violation
            \Log::warning('Security violation: Non-admin user attempted admin access', [
                'user_id' => $user->id ?? null,
                'user_role' => $user->role ?? null,
                'route' => request()->route()?->getName(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            abort(403, 'Admin privileges required');
        }
    }

    /**
     * Check if current user has specific role
     */
    protected function hasRole(string $role): bool
    {
        return auth()->check() && auth()->user()->role === $role;
    }

    /**
     * Ensure user can only access their own role areas
     */
    protected function enforceRoleRestriction(string $requiredRole)
    {
        if (!$this->hasRole($requiredRole)) {
            $userRole = auth()->user()->role ?? 'unknown';
            
            // Log the unauthorized attempt
            \Log::warning('Role restriction violation', [
                'user_id' => auth()->id(),
                'user_role' => $userRole,
                'required_role' => $requiredRole,
                'route' => request()->route()?->getName(),
            ]);

            // Redirect to appropriate dashboard
            return $this->redirectToUserDashboard($userRole);
        }
    }

    /**
     * Redirect user to their appropriate dashboard
     */
    protected function redirectToUserDashboard(string $userRole)
    {
        $redirectMap = [
            'admin' => '/admin/home',
            'inventory' => '/inventory/home',
            'pharmacy' => '/pharmacy/home',
            'doctor' => '/doctor/home',
            'nurse' => '/nurse/home',
            'lab_technician' => '/labtech/home',
            'cashier' => '/cashier/home',
        ];

        $redirectUrl = $redirectMap[$userRole] ?? '/login';
        return redirect($redirectUrl)->with('error', 'Access denied. You can only access areas for your role.');
    }
}