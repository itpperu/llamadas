<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateExternalApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearer = $request->bearerToken();

        if (!$bearer) {
            return response()->json(['error' => 'Token de autenticación requerido.'], 401);
        }

        $apiToken = ApiToken::findByPlainToken($bearer);

        if (!$apiToken || !$apiToken->isValid()) {
            return response()->json(['error' => 'Token inválido o revocado.'], 401);
        }

        $apiToken->update(['last_used_at' => now()]);

        return $next($request);
    }
}
