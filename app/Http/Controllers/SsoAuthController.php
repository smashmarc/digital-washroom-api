<?php
namespace App\Http\Controllers;
use App\Constants\AdminUsers;
use App\Helpers\ApiResponse;
use App\Http\Resources\AuthResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;


class SsoAuthController extends Controller
{
    public function login(Request $request)
    {
        $token = $request->bearerToken();

        $payload = json_decode(
            base64_decode(explode('.', $token)[1]),
            true
        );

        // $user = User::updateOrCreate(
        //     [
        //         'azure_oid' => $payload['oid'],
        //     ],
        //     [
        //         'name' => $payload['name'],
        //         'email' => $payload['upn'] ?? $payload['unique_name'],
        //         'azure_tenant_id' => $payload['tid'],
        //     ]
        // );
        $userIdentifier = $payload['upn'] ?? $payload['preferred_username'] ?? $payload['email'];

        $user = User::firstOrNew(['azure_oid' =>  $payload['oid']]);
        $user->azure_oid = $payload['oid']; 
        $user->name = $payload['name'];
        $user->password = bcrypt("password"); //fake password
        $user->email = $userIdentifier;
        $user->azure_tenant_id = $payload['tid'];
        $user->username = $userIdentifier;
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

       // --- Get user roles and permissions for frontend ---
        $userPayload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'azure_oid' => $user->azure_oid,
            'azure_tenant_id' => $user->azure_tenant_id,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];

         $request->setUserResolver(fn() => $user);

        //return ApiResponse::success('SSO login successful', $userPayload);

        return ApiResponse::success('SSO login successful', new AuthResource([
        'user'         => $userPayload,
        'access_token' => '',
        'token_type'   => 'bearer',
        'expires_in'   => 60
        ]));
    }
}