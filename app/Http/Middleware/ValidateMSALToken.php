<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\JWK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateMSALToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Extract token from Authorization header
            $token = $this->extractToken($request);
            
            if (!$token) {
                return response()->json([
                    'error' => 'Authorization token missing',
                    'message' => 'Please provide a valid Bearer token'
                ], 401);
            }

            // Validate the token
            $decoded = $this->validateToken($token);
            
            // Add decoded token data to request
            $request->merge(['user_data' => $decoded]);
            $request->merge(['msal_token' => $token]);

            return $next($request);

        } catch (Exception $e) {
            Log::error('MSAL Token validation failed: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Token validation failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Extract token from Authorization header
     */
    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7);
    }

    /**
     * Validate MSAL JWT token
     */
    private function validateToken(string $token): object
    {
        // Decode token header to get key ID
        $header = $this->decodeTokenHeader($token);
        
        if (!isset($header->kid)) {
            throw new Exception('Token missing key ID (kid)');
        }

        // Get JWKS and find the appropriate key
        $jwks = $this->getJWKS();
        $key = $this->findKey($jwks, $header->kid);
        
        if (!$key) {
            throw new Exception('Unable to find matching key');
        }

        // Verify and decode the token
        $decoded = JWT::decode($token, new Key($key['x5c'][0], 'RS256'));
        
        // Validate claims
        $this->validateClaims($decoded);
        
        return $decoded;
    }

    /**
     * Decode token header without verification
     */
    private function decodeTokenHeader(string $token): object
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        $header = json_decode(base64_decode($parts[0]));
        
        if (!$header) {
            throw new Exception('Invalid token header');
        }

        return $header;
    }

    /**
     * Get JWKS from Microsoft
     */
    private function getJWKS(): array
    {
        $tenantId = config('services.azure.tenant_id');
        $cacheKey = "msal_jwks_{$tenantId}";
        
        // Try to get from cache first
        $jwks = Cache::get($cacheKey);
        
        if (!$jwks) {
            $jwksUri = "https://login.microsoftonline.com/{$tenantId}/discovery/v2.0/keys";
            
            $response = Http::timeout(30)->get($jwksUri);
            
            if (!$response->successful()) {
                throw new Exception('Failed to fetch JWKS');
            }

            $jwks = $response->json();
            
            // Cache for 10 minutes
            Cache::put($cacheKey, $jwks, 600);
        }

        return $jwks;
    }

    /**
     * Find key in JWKS by key ID
     */
    private function findKey(array $jwks, string $kid): ?array
    {
        foreach ($jwks['keys'] as $key) {
            if ($key['kid'] === $kid) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Validate token claims
     */
    private function validateClaims(object $decoded): void
    {
        $now = time();
        $tenantId = config('services.azure.tenant_id');
        $clientId = config('services.azure.client_id');

        // Check expiration
        if (!isset($decoded->exp) || $decoded->exp < $now) {
            throw new Exception('Token has expired');
        }

        // Check not before
        if (isset($decoded->nbf) && $decoded->nbf > $now) {
            throw new Exception('Token not yet valid');
        }

        // Check issued at
        if (isset($decoded->iat) && $decoded->iat > $now + 300) { // 5 minute clock skew
            throw new Exception('Token used before issued');
        }

        // Validate audience
        if (!isset($decoded->aud) || $decoded->aud !== $clientId) {
            throw new Exception('Invalid audience');
        }

        // Validate issuer
        $expectedIssuers = [
            "https://sts.windows.net/{$tenantId}/",
            "https://login.microsoftonline.com/{$tenantId}/v2.0"
        ];

        if (!isset($decoded->iss) || !in_array($decoded->iss, $expectedIssuers)) {
            throw new Exception('Invalid issuer');
        }

        // Validate tenant
        if (isset($decoded->tid) && $decoded->tid !== $tenantId) {
            throw new Exception('Invalid tenant');
        }
    }
}
