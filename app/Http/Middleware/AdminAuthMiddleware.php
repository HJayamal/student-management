<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check whether the user is logged in
        if (! $request->session()->has('student_id')) {
            return redirect()->route('login');
        }

        // Only admins can access admin-protected routes
        if ($request->session()->get('student_role') !== 'admin') {
            abort(403, 'Access denied. Admin permission is required.');
        }

        return $next($request);
    }
}
