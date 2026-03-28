<?php

namespace App\Http\Middleware;

use App\Constants\AdminUsers;
use App\Models\Role;
use App\Models\User;
use Closure;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Constants\Role as ConstantRole;

class EntraTokenMiddlewareBak
{
     public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Missing bearer token'], 401);
        }

        try {
            $appEnvLocal = config('app.env') === 'local';
            
            $tenantId = config('app.env') === 'local'
        ? '50fbc7ae-9c33-43e8-a372-8fdaa1c7712e' // local dev tenant (optional)
        : config('services.entra.tenant_id');

            
                // --- PROD MODE: fetch Microsoft JWKS (cached) ---
                $jwksResponse = cache()->remember("entra_jwks_{$tenantId}", 3600, function () use ($token, $tenantId) {
                    return Http::get(
                        'https://login.microsoftonline.com/' . $tenantId. '/discovery/v2.0/keys'
                    );
                });

                if (!$jwksResponse->ok()) {
                    return response()->json(['message' => 'Failed to fetch Microsoft JWKS'], 500);
                }

                $jwks = $jwksResponse->json();

                if (!is_array($jwks) || !isset($jwks['keys'])) {
                    return response()->json(['message' => 'Invalid JWKS format'], 500);
                }

                $keys = JWK::parseKeySet($jwks);
                $decoded = JWT::decode($token, $keys);

                Log::debug('decoded payload token:', $decoded);

                $payload = (array) $decoded;
            

    //     $userIdentifier = $payload['upn'] ?? $payload['preferred_username'] ?? $payload['email'];

    //     $user = User::firstOrNew(['azure_oid' =>  $payload['oid']]);
    //     $user->azure_oid = $payload['oid']; 
    //     $user->name = $payload['name'];
    //     $user->password = bcrypt("password"); //fake password
    //     $user->email = $userIdentifier;
    //     $user->azure_tenant_id = $payload['tid'];
    //     $user->username = $userIdentifier;
    //     $user->save();
        
      
    //     //only for web guard
    //     //auth()->login($user);

    //     // --- Assign default role "staff" ---
    //     // ---- Assign role based on whitelist ----
    //     if (in_array($user->email, AdminUsers::WHITELIST)) {
    //         $role = Role::firstOrCreate(['name' => 'administrator']);
    //     } else {
    //         $role = Role::firstOrCreate(['name' => 'staff']);
    //     }
        
    //    $user->syncRoles([$role->name]);

    //         // --- Attach user to request & auth system ---
    //         $request->setUserResolver(fn() => $user);
    //         auth()->guard('entra')->setUser($user); 

        } catch (\Exception $e) {
            Log::error('EntraTokenMiddleware error', ['exception' => $e->getMessage()]);
            return response()->json([
                'message' => 'Invalid token',
                'error' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}

