<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JWTVerify
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'user not found'], 401);
            }
        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['message' => 'Token inválido'], 401);
            }
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['message' => 'Token expirado'], 401);
            }
            return response()->json(['message' => 'Token não encontrado'], 401);
        }
        return $next($request);
    }
}
