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
            return redirect('/login');
        }

        // Get the user's role
        $userRole = auth()->user()->role;

        // Check if user has the required role
        if ($userRole !== $role) {
            // Redirect to user's own dashboard based on their role
            switch ($userRole) {
                case 'admin':
                    return redirect('/admin/home')->with('error', 'Access denied. You can only access admin areas.');
                case 'doctor':
                    return redirect('/doctor/home')->with('error', 'Access denied. You can only access doctor areas.');
                case 'nurse':
                    return redirect('/nurse/home')->with('error', 'Access denied. You can only access nurse areas.');
                case 'lab_technician':
                    return redirect('/labtech/home')->with('error', 'Access denied. You can only access lab technician areas.');
                case 'cashier':
                    return redirect('/cashier/home')->with('error', 'Access denied. You can only access cashier areas.');
                case 'inventory':
                    return redirect('/inventory')->with('error', 'Access denied. You can only access inventory areas.');
                case 'pharmacy':
                    return redirect('/pharmacy/home')->with('error', 'Access denied. You can only access pharmacy areas.');
                case 'billing':
                    return redirect('/billing/home')->with('error', 'Access denied. You can only access billing areas.');
                default:
                    return redirect('/login')->with('error', 'Invalid role. Please contact administrator.');
            }
        }

        return $next($request);
    }
}
