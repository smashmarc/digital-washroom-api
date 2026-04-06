<?php

namespace App\Http\Controllers;

use App\Constants\PermissionConstant;
use App\Helpers\ApiResponse;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
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
        $columns = ['name'];
        try {
            $roles = $this->userService->searchPaginatedList($params, $columns);
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
        }else{
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
        $user->delete();

        return ApiResponse::success('User deleted successfully', null, 200);
    }

    public function upload(Request $request): JsonResponse
    {
        // Only allow admins to upload
        Gate::authorize('create', User::class);

        try {
            // Validate input: expect an array of users
            $data = $request->validate([
                'users' => 'required|array|min:1',
                'users.*.name' => 'required|string|max:255',
                'users.*.email' => 'required|email|unique:users,email',
                'users.*.password' => 'nullable|string|min:6', // optional, will hash if provided
                'users.*.roles' => 'nullable|array',
                'users.*.roles.*' => 'string',
                'users.*.location'=> 'string'
            ]);

            $usersData = collect($data['users'])->map(function ($user) {
                // Hash password if provided, else generate random
                $user['password'] = isset($user['password']) && $user['password']
                    ? Hash::make($user['password'])
                    : Hash::make("password");

                return $user;
            })->toArray();

            // Call the service
            $createdUsers = $this->userService->upload($usersData);

            return ApiResponse::success(
                'Users uploaded successfully.',
                $createdUsers, // optionally wrap in Resource
                201
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed.', 422, $e->errors());
        } catch (\Exception $e) {
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
