<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->email !== config('app.admin_email', 'admin@example.com')) {
            abort(403, 'Admin access is required.');
        }

        return $next($request);
    }
}
