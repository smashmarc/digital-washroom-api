<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UploadUserRequest;
use App\Http\Resources\EvaluationResource;
use App\Http\Resources\UserFormOptionsResource;
use App\Http\Resources\UserResource;
use App\Models\Evaluation;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function evaluations(User $user): JsonResponse
    {
        Gate::authorize('view', $user);
        $evaluations = Evaluation::where('user_id', $user->id)
            ->with(['template', 'evaluator'])
            ->orderBy('id', 'desc')
            ->paginate(50);
        return ApiResponse::success('User evaluations fetched successfully.', $evaluations, 200, EvaluationResource::class);
    }

    public function options(): JsonResponse
    {
        $items = User::orderBy('name')->get(['id', 'name']);
        return ApiResponse::success('User options fetched successfully.', $items);
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view', User::class);
        $params = $request->only(['search', 'sort_by', 'sort_dir', 'per_page', 'with', 'columns', 'exact', 'department_id']);
        $users = $this->userService->searchPaginatedList($params);
        return ApiResponse::success('Users fetched successfully.', $users, 200, UserResource::class);
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        Gate::authorize('create', User::class);
        $user = $this->userService->create($request->validated());
        return ApiResponse::success('User created successfully.', new UserResource($user), 201);
    }

    public function show(User $user): JsonResponse
    {
        Gate::authorize('view', User::class);
        $user->load(['roles', 'location', 'departments']);
        return ApiResponse::success('User fetched successfully.', new UserResource($user));
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
        $updatedUser = $this->userService->update($validated, $user);
        return ApiResponse::success('User updated successfully.', new UserResource($updatedUser));
    }

    public function getFormOptions(): JsonResponse
    {
        Gate::authorize('view', User::class);
        $formOptions = $this->userService->getFormOptions();
        return ApiResponse::success('Form options fetched.', new UserFormOptionsResource($formOptions));
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return ApiResponse::error('You cannot delete your own account.', 403);
        }
        Gate::authorize('delete', $user);
        $user->delete();
        return ApiResponse::success('User deleted successfully.', null, 200);
    }

    public function upload(UploadUserRequest $request): JsonResponse
    {
        Gate::authorize('create', User::class);
        $validated    = $request->validated();
        $hasOverride  = !empty($validated['default_password']);
        $overrideHash = $hasOverride ? Hash::make($validated['default_password']) : null;
        $fallbackHash = $overrideHash ?? Hash::make('Welcome123');

        $usersData = collect($validated['users'])->map(function ($user) use ($hasOverride, $overrideHash, $fallbackHash) {
            $user['password'] = $hasOverride
                ? $overrideHash
                : (!empty($user['password']) ? Hash::make($user['password']) : $fallbackHash);
            return $user;
        })->toArray();

        $createdUsers = $this->userService->upload($usersData);
        return ApiResponse::success('Users uploaded successfully.', $createdUsers, 201);
    }
}
