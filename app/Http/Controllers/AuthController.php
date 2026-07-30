<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Resources\AuthResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * User login with JWT.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return ApiResponse::error(
                'Incorrect username or password',
                401,
                ['username' => ['These credentials do not match our records.']]
            );
        }

        $user = auth()->user();
        if (!$user->is_active) {
            auth()->logout();
            return ApiResponse::error(
                'Your account has been deactivated. Please contact an administrator.',
                403,
                null,
                'ACCOUNT_DEACTIVATED'
            );
        }

        return $this->respondWithToken($token, 'Login successful', $user);
    }

    /**
     * Get current authenticated user.
     */
    public function me()
    {
        $user = auth()->user()->load('location', 'departments');

        return ApiResponse::success('User profile', (object) [
            'id'                    => $user->id,
            'name'                  => $user->name,
            'email'                 => $user->email,
            'username'              => $user->username,
            'force_password_change' => $user->force_password_change,
            'roles'                 => $user->getRoleNames()->toArray(),
            'permissions'           => $user->getAllPermissions()->pluck('name')->toArray(),
            'location'              => $user->location ? ['id' => $user->location->id, 'name' => $user->location->name] : null,
            'departments'           => $user->departments->map(fn($d) => ['id' => $d->id, 'name' => $d->name])->values(),
            'created_at'            => $user->created_at,
            'updated_at'            => $user->updated_at,
        ]);
    }

    /**
     * Logout (invalidate token).
     */
    public function logout()
    {
        auth()->logout();

        return ApiResponse::success('Successfully logged out', null);
    }

    /**
     * Refresh JWT token.
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh(), 'Token refreshed successfully', auth()->user());
    }

    /**
     * Format token response consistently.
     */
    protected function respondWithToken(string $token, string $message, $user = null)
    {
        return ApiResponse::success($message, new AuthResource([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60, // TTL is in minutes → convert to seconds
        ]));
    }

    public function forceChangePassword(ChangePasswordRequest $request)
    {


        $user = $request->user(); // cleaner than auth()->user()

        DB::beginTransaction();

        try {
            // ✅ Update password + reset flag
            $user->password = Hash::make($request->new_password);
            $user->force_password_change = 0;
            $user->save();

            // ✅ Issue NEW TOKEN (invalidate old one if needed)
            $token = auth()->login($user);

            DB::commit();

            return ApiResponse::success("success", new AuthResource([
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60, // TTL is in minutes → convert to seconds
        ]));
        } catch (\Throwable $e) {
            DB::rollBack();

          
            return ApiResponse::error(
                'Failed to change password. ' . $e->getMessage(),
                500
            );
        }
    }
}
