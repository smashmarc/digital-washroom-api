<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Exception;

class UsersController extends Controller
{

    public function __construct(protected UserService $userService) {}

    
    public function index(Request $request): JsonResponse
    {

        $params = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
            'with',
            'columns',
            'exact'
        ]);
        $columns = ['name'];
        try {
            $roles = $this->userService->searchPaginatedList($params, $columns);
            return ApiResponse::paginated(
                'User fetched successfully.',
                $roles,
                UserResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch roles.', 500);
        }
    }

    public function store(CreateUserRequest $request): JsonResponse
    {

        try {
            $user = $this->userService->create($request->validated());
            return ApiResponse::success(
                'User created successfully.',
                new UserResource($user)
            );
        } catch (Exception $e) {
            return ApiResponse::error('Something went wrong. please contact your administrator', 500);
        }
    }


    public function show(User $user): JsonResponse
    {
        return ApiResponse::success('User fetched successfully', new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        try {
            $updatedUser = $this->userService->update($validated, $user);

            return ApiResponse::success(
                'User updated successfully.',
                new UserResource($updatedUser)
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update user.', 500);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return ApiResponse::success('User deleted successfully', null, 200);
    }
}
