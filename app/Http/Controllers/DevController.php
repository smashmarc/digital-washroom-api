<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Constants\Role as RoleConstant;

class DevController extends Controller
{
    /**
     * Create a super admin user (dev-only).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSuperAdmin(Request $request)
    {
        $secret = $request->header('X-DEV-KEY'); // OR $request->input('key')

        if ($secret !== config('app.dev_superadmin_key')) {
            return ApiResponse::error('Unauthorized', 401);
        }

        // check if already exists
        if (User::where('email', 'superadmin@example.com')->exists()) {
            return ApiResponse::error('Super admin already exists', 409);
        }

        $user = User::create([
            'id'=>1,
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
            // assuming you have roles column
        ]);

            $role = Role::firstOrCreate(['name' => RoleConstant::ADMINISTRATOR, 'guard_name' => 'api']);
             $user->assignRole($role);

        return ApiResponse::success('Super admin created successfully', $user);
    }
}
