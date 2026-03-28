<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\JWK;
use Symfony\Component\HttpFoundation\Response;

class EntraTokenMiddleware
{
    /**
     * Microsoft Entra ID JWKS URI (public keys endpoint).
     * Keys are cached to avoid repeated HTTP requests.
     */
    private string $jwksUri;
    private string $tenantId;
    private string $clientId;
    private string $issuerBase;
    private string $msaJwksUri;

    public function __construct()
    {
        // $this->tenantId   = config('entra.tenant_id');
        // $this->clientId   = config('entra.client_id');
        $this->tenantId   = "50fbc7ae-9c33-43e8-a372-8fdaa1c7712e";
        $this->clientId   = "d8abeb6f-44ff-4c5a-9823-cb67d345564f";
        // v2.0 issuer for your own tenant
        $this->issuerBase = "https://login.microsoftonline.com/{$this->tenantId}/v2.0";

        // Primary JWKS: your tenant
        $this->jwksUri = "https://login.microsoftonline.com/{$this->tenantId}/discovery/v2.0/keys";

        // Fallback JWKS: Microsoft personal accounts (MSA) — tid 9188040d-...
        $this->msaJwksUri = "https://login.microsoftonline.com/9188040d-6c67-4c5b-b112-36a304b66dad/discovery/v2.0/keys";
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$requiredRoles): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return $this->unauthorized('Missing or malformed Authorization header.');
        }

        try {
            $claims = $this->validateToken($token);
        } catch (\Exception $e) {
            Log::warning('Entra ID token validation failed', ['error' => $e->getMessage()]);
            return $this->unauthorized('Token validation failed: ' . $e->getMessage());
        }

        // Role/scope check (optional — pass roles via middleware params)
        if (!empty($requiredRoles)) {
            $tokenRoles = (array) ($claims->roles ?? []);
            $missing    = array_diff($requiredRoles, $tokenRoles);

            if (!empty($missing)) {
                return $this->forbidden('Insufficient roles: ' . implode(', ', $missing));
            }
        }

        // Attach decoded claims to the request for downstream use
        $request->attributes->set('entra_claims', $claims);
        $request->attributes->set('entra_user_id', $claims->oid ?? $claims->sub ?? null);

        return $next($request);
    }

    // -------------------------------------------------------------------------
    // Token extraction
    // -------------------------------------------------------------------------

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Token validation
    // -------------------------------------------------------------------------

    private function validateToken(string $token): object
    {
        // Peek at the token's issuer before full validation
        $unverified = $this->peekClaims($token);
        $iss        = $unverified['iss'] ?? '';

        $msaTenantId = '9188040d-6c67-4c5b-b112-36a304b66dad';
        $isMsa       = str_contains($iss, $msaTenantId);

        // Pick the right JWKS source
        $jwks = $isMsa ? $this->getJwks($this->msaJwksUri, 'entra_jwks_msa')
            : $this->getJwks($this->jwksUri, 'entra_jwks');

        $keys    = $this->parseJwksKeys($jwks);
        $decoded = JWT::decode($token, $keys);

        // Validate issuer
        $validIssuers = [
            $this->issuerBase,
            "https://sts.windows.net/{$this->tenantId}/",
            "https://login.microsoftonline.com/{$msaTenantId}/v2.0", // MSA issuer
        ];

        if (!in_array($decoded->iss, $validIssuers, true)) {
            throw new \RuntimeException("Invalid issuer: {$decoded->iss}");
        }

        // Validate audience
        $aud = is_array($decoded->aud) ? $decoded->aud : [$decoded->aud];
        if (!in_array($this->clientId, $aud, true)) {
            throw new \RuntimeException('Token audience does not match client ID.');
        }

        return $decoded;
    }

    /**
     * Decode the JWT payload WITHOUT verifying the signature.
     * Used only to peek at the issuer so we know which JWKS to fetch.
     */
    private function peekClaims(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \RuntimeException('Malformed JWT.');
        }

        $payload = base64_decode(str_pad(
            strtr($parts[1], '-_', '+/'),
            strlen($parts[1]) % 4,
            '=',
            STR_PAD_RIGHT
        ));

        return json_decode($payload, true) ?? [];
    }


    private function parseJwksKeys(array $jwks): array
    {
        $keys = [];

        foreach ($jwks['keys'] ?? [] as $keyData) {
            // Skip keys not suitable for signing
            if (isset($keyData['use']) && $keyData['use'] !== 'sig') {
                continue;
            }

            // Inject alg if missing — Entra ID uses RS256
            if (!isset($keyData['alg'])) {
                $keyData['alg'] = 'RS256';
            }

            try {
                $kid      = $keyData['kid'] ?? $keyData['n'] ?? uniqid();
                $parsedSet = JWK::parseKeySet(['keys' => [$keyData]]);

                foreach ($parsedSet as $keyId => $key) {
                    $keys[$kid] = $key;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse JWK key', ['kid' => $keyData['kid'] ?? 'unknown', 'error' => $e->getMessage()]);
            }
        }

        if (empty($keys)) {
            throw new \RuntimeException('No valid signing keys found in JWKS.');
        }

        return $keys;
    }

    // -------------------------------------------------------------------------
    // JWKS fetching with cache
    // -------------------------------------------------------------------------

    private function getJwks(string $uri, string $cacheKey): array
    {
        return Cache::remember($cacheKey, now()->addHours(12), function () use ($uri) {
            $response = Http::timeout(10)->get($uri);

            if (!$response->ok()) {
                throw new \RuntimeException("Failed to fetch JWKS from: {$uri}");
            }

            return $response->json();
        });
    }

    // -------------------------------------------------------------------------
    // Response helpers
    // -------------------------------------------------------------------------

    private function unauthorized(string $message): Response
    {
        return response()->json(['error' => 'Unauthorized', 'message' => $message], 401);
    }

    private function forbidden(string $message): Response
    {
        return response()->json(['error' => 'Forbidden', 'message' => $message], 403);
    }
}
