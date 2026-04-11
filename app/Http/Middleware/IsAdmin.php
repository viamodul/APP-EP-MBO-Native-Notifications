<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminEmails = array_map('trim', explode(',', env('ADMIN_EMAILS', '')));

        if (!$request->user() || !in_array($request->user()->email, array_filter($adminEmails))) {
            abort(403);
        }

        return $next($request);
    }
}
