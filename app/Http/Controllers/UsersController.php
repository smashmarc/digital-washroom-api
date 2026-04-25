<?php

namespace App\Http\Controllers;

use App\Constants\PermissionConstant;
use App\Helpers\ApiResponse;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UploadUserRequest;
use App\Http\Resources\UserFormOptionsResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class UsersController extends Controller
{

    public function __construct(protected UserService $userService) {}


    public function index(Request $request): JsonResponse
    {

        Gate::authorize('view', User::class);

        $params = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'per_page',
            'with',
            'columns',
            'exact'
        ]);

        try {
            $roles = $this->userService->searchPaginatedList($params);
            return ApiResponse::success(
                'User fetched successfully.',
                $roles,
                200,
                UserResource::class
            );
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch roles.', 500);
        }
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        //simulate unauthorized respnse here
        //return ApiResponse::error('Unauthorized', 401);
        try {
            $user = $this->userService->create($request->validated());
            return ApiResponse::success(
                'User created successfully.',
                new UserResource($user),
                201
            );
        } catch (Exception $e) {
            return ApiResponse::error('Something went wrong. please contact your administrator', 500);
        }
    }



    public function show(User $user): JsonResponse
    {
        Gate::authorize('view', User::class);
        $user->load('roles');
        return ApiResponse::success('User fetched successfully', new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        Gate::authorize('update', $user);
        $validated = $request->validated();

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
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

    public function getFormOptions()
    {
        Gate::authorize('update', User::class);
        try {

            $formOptions = $this->userService->getFormOptions();

            return ApiResponse::success(
                'Form options fetched.',
                new UserFormOptionsResource($formOptions)
            );
        } catch (\Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error('Failed to fetch Form Options.', 500);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);
        try {
            $user->delete();
            return ApiResponse::success('User deleted successfully', null, 200);
        } catch (Exception $e) {
            Log::error(__METHOD__ . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error('Failed to delete user.', 500);
        }
    }

    public function upload(UploadUserRequest $request): JsonResponse
    {

        Gate::authorize('create', User::class);

       try {
        $validated       = $request->validated();
        $defaultPassword = !empty($validated['default_password'])
            ? Hash::make($validated['default_password']) // hash once
            : null;

        $usersData = collect($validated['users'])->map(function ($user) use ($defaultPassword) {
            $user['password'] = $defaultPassword
                ?? Hash::make($user['password'] ?? 'password'); // hash per row only if no default
            return $user;
        })->toArray();

        $createdUsers = $this->userService->upload($usersData);

        return ApiResponse::success('Users uploaded successfully.', $createdUsers, 201);

    }  catch (\Exception $e) {
            Log::error('Failed to upload users: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ApiResponse::error(
                'Something went wrong while uploading users. Please contact your administrator.',
                500
            );
        }
    }
}
