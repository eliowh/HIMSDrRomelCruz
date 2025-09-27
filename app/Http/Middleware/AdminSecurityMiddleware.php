<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSecurityMiddleware
{
    /**
     * Handle an incoming request.
     * Additional security layer for admin routes
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'You must be logged in to access this area.');
        }

        // Check if user has admin role
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            // Log the unauthorized access attempt
            \Log::warning('Unauthorized admin access attempt', [
                'user_id' => $user->id ?? null,
                'user_role' => $user->role ?? null,
                'requested_url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Show 403 error instead of redirecting to prevent loops
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Additional security checks
        if ($request->ajax() && !$request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}