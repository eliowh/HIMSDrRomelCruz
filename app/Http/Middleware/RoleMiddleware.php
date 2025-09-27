<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Please log in to access this area.');
        }

        // Get the user's role
        $user = auth()->user();
        $userRole = $user->role;

        // Check if user has the required role
        if ($userRole !== $role) {
            // Log the unauthorized access attempt
            \Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'required_role' => $role,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);

            // Create a user-friendly error message
            $errorMessage = "Access denied. This area requires {$role} privileges.";
            
            // For AJAX requests, return JSON response
            if ($request->expectsJson()) {
                return response()->json(['error' => $errorMessage], 403);
            }
            
            // For regular requests, show 403 error page
            abort(403, $errorMessage);
        }

        return $next($request);
    }
}
