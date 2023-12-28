<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {


        try {
            if (!auth($guard)->user()) {
                return response()->json([
                    'status' => false,
                    'message' => "Unauthorized!"
                ], 401);
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid token!',
                ], 401);
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your session has expired. Please log in again.',
                ], 401);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Token not found',
                ], 404);
            }
        }

        return $next($request);
    }
}
