<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en', 'de'];

        if ($request->user() && in_array($request->user()->locale, $supported)) {
            app()->setLocale($request->user()->locale);
        } elseif (session()->has('locale') && in_array(session('locale'), $supported)) {
            app()->setLocale(session('locale'));
        }

        return $next($request);
    }
}
