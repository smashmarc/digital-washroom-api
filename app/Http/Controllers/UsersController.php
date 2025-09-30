<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Constants\PermissionConstant;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserFormOptionsResource;

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
}
