<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Cache;
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
            $issuer = (string) config('services.identity.issuer');
            $audience = (string) config('services.identity.audience');
            $jwksCacheTtl = (int) config('services.identity.jwks_cache_ttl', 3600);
            $httpTimeout = (int) config('services.identity.http_timeout_seconds', 5);

            if (trim($issuer) === '') {
                return response()->json(['error' => 'Identity issuer is not configured'], 500);
            }

            if (trim($audience) === '') {
                return response()->json(['error' => 'Identity audience is not configured'], 500);
            }

            // Local JWT validation with cached JWKS from logical issuer.
            $jwks = Cache::remember(
                'identity_jwks_' . md5($issuer),
                now()->addSeconds($jwksCacheTtl),
                function () use ($issuer, $httpTimeout) {
                    $response = Http::timeout($httpTimeout)
                        ->acceptJson()
                        ->get(rtrim($issuer, '/') . '/.well-known/jwks.json');

                    if (!$response->ok()) {
                        throw new \RuntimeException('Unable to fetch JWKS from identity issuer');
                    }

                    $payload = $response->json();
                    if (!is_array($payload) || !isset($payload['keys']) || !is_array($payload['keys'])) {
                        throw new \RuntimeException('Invalid JWKS payload');
                    }

                    return $payload;
                }
            );

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

            if (!isset($decoded->tenant_id)) {
                return response()->json(['error' => 'Missing tenant_id claim'], 401);
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