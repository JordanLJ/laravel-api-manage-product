<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyIdentityToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Missing token'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);

        try {

            $issuer = config('services.identity.issuer');
            $audience = config('services.identity.audience');

            // récupérer les clés publiques JWKS
            $jwks = Http::get($issuer . '/.well-known/jwks.json')->json();

            $keys = JWK::parseKeySet($jwks);

            $decoded = JWT::decode($token, $keys);

            // Vérifier issuer
            if ($decoded->iss !== $issuer) {
                return response()->json(['error' => 'Invalid issuer'], 401);
            }

            // Vérifier audience
            if (!in_array($audience, (array)$decoded->aud)) {
                return response()->json(['error' => 'Invalid audience'], 401);
            }

            // stocker l'utilisateur dans la requête
            $request->attributes->set('user', $decoded);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}