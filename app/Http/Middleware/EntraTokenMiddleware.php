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

class EntraTokenMiddleware
{
     public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Missing bearer token'], 401);
        }

        try {
            $appEnvLocal = config('app.env') === 'local';
            
            if ($appEnvLocal) {
                // --- DEV MODE: use fake MSAL token ---
                $payload = json_decode(base64_decode(explode('.', $token)[1] ?? ''), true);

                if (!$payload || !isset($payload['oid'], $payload['upn'])) {
                    return response()->json(['message' => 'Invalid token payload'], 401);
                }
            } else {
                // --- PROD MODE: fetch Microsoft JWKS (cached) ---
                $jwksResponse = cache()->remember('entra_jwks', 3600, function () use ($token) {
                    return Http::get(
                        'https://login.microsoftonline.com/' . config('services.entra.tenant_id') . '/discovery/v2.0/keys'
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

                $payload = (array) $decoded;
            }

            
        $user = User::firstOrNew(['azure_oid' =>  $payload['oid']]);
        $user->azure_oid = $payload['oid']; 
        $user->name = $payload['name'];
        $user->password = bcrypt("password"); //fake password
        $user->email = $payload['upn'];
        $user->azure_tenant_id = $payload['tid'];
        $user->username = $payload['upn'];
        $user->save();
        //only for web guard
        //auth()->login($user);

        // --- Assign default role "staff" ---
        // ---- Assign role based on whitelist ----
        if (in_array($user->email, AdminUsers::WHITELIST)) {
            $role = Role::firstOrCreate(['name' => 'administrator']);
        } else {
            $role = Role::firstOrCreate(['name' => 'staff']);
        }
        
       $user->syncRoles([$role->name]);

            // --- Attach user to request & auth system ---
            $request->setUserResolver(fn() => $user);
            auth()->guard('entra')->setUser($user); 

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

