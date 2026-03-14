<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AppToken;

class EnsureValidAppToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $providedToken = $request->bearerToken();

        if (empty($providedToken)) {
            return response()->json([
                'message' => 'Unauthorized. Token not provided.'
            ], 401);
        }

        $appToken = AppToken::where('token', $providedToken)
            ->where('active', true)
            ->first();

        if (!$appToken) {
            return response()->json([
                'message' => 'Unauthorized or invalid token.'
            ], 401);
        }

        // Update last used timestamp
        $appToken->update(['last_used_at' => now()]);

        return $next($request);
    }
}
