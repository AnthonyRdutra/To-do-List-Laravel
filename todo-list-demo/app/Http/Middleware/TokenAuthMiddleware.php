<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class TokenAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Token ausente.'], 401);
        }

        $token = trim(substr($header, 7)); // remove "Bearer "

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token inválido.'], 401);
        }

        auth()->setUser($user);

        return $next($request);
    }
}
