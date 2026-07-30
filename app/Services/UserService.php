<?php

namespace App\Services;

use Exception;
use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use App\Models\Location;
use App\Constants\Role as RoleConstant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function searchPaginatedList(array $params = [], $columns = [])
    {
        $sortDir = in_array(strtolower($params['sort_dir'] ?? ''), ['asc', 'desc'])
            ? strtolower($params['sort_dir'])
            : 'asc';

        $query = User::query();

        if (!empty($params['with']) && is_array($params['with'])) {
            $query->with($params['with']);
        }

        if (!empty($params['department_id'])) {
            $query->whereHas('departments', fn($q) => $q->where('departments.id', (int) $params['department_id']));
        }

        if (!empty($params['search'])) {
            $search = trim($params['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('roles', fn($r) => $r->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('location', fn($l) => $l->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('departments', fn($d) => $d->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy($params['sort_by'] ?? 'id', $sortDir)
                     ->paginate(max(1, (int) ($params['per_page'] ?? 10)));
    }

    public function getFormOptions()
    {
        try {
            $isAdmin = Auth::user()?->hasRole(RoleConstant::ADMINISTRATOR);

            $roles = Role::when(!$isAdmin, function ($query) {
                $query->where('name', '!=', RoleConstant::ADMINISTRATOR);
            })->get();

            $locations = Location::all();
            $departments = Department::orderBy('name')->get();
            return ['roles' => $roles, 'locations' => $locations, 'departments' => $departments];
        } catch (Exception $e) {
            Log::error('Failed to fetch form options ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }



    public function create(array $data): User
    {
        DB::beginTransaction();
        try {
            $user = User::create($data);
            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }
            if (isset($data['departments'])) {
                $user->departments()->sync($data['departments']);
            }
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create role: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function update(array $data, \App\Models\User $user)
    {
        DB::beginTransaction();

        try {
            // Update user fields          
            $user->update($data);
            if (isset($data['roles'])) {
                $user->syncRoles($data['roles']);
            }
            if (isset($data['departments'])) {
                $user->departments()->sync($data['departments']);
            }

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('UserService update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'user_id' => $user->id,
            ]);
            throw $e; // controller will handle ApiResponse
        }
    }


    public function upload(array $usersData): array
    {
        $createdUsers = [];

        DB::beginTransaction();
        try {
            foreach ($usersData as $data) {
                // 🔥 Handle location mapping
            if (!empty($data['location'])) {
                $location = Location::where('name', $data['location'])->first();

                if ($location) {
                    $data['location_id'] = $location->id;
                } else {
                    Log::warning('Location not found', [
                        'input_location' => $data['location'],
                        'user_email' => $data['email'] ?? null,
                    ]);
                }

                // optional: remove raw location field
                //unset($data['location']);
            }

                // Create user
                Log::debug("create data", $data);
                $user = User::create($data);

                // Assign roles if provided
                 if (!empty($data['roles']) && is_array($data['roles'])) {

                // Get only existing roles from DB
                $validRoles = Role::whereIn('name', $data['roles'])
                    ->pluck('name')
                    ->toArray();


                if (!empty($validRoles)) {
                    $user->syncRoles($validRoles);
                } else {
                    $user->syncRoles(["staff"]);
                }
            }

                // Assign departments if provided
                if (!empty($data['departments']) && is_array($data['departments'])) {
                    $departmentIds = Department::whereIn('name', $data['departments'])
                        ->pluck('id')
                        ->toArray();

                    if (!empty($departmentIds)) {
                        $user->departments()->sync($departmentIds);
                    } else {
                        Log::warning('No matching departments found', [
                            'input_departments' => $data['departments'],
                            'user_email' => $data['email'] ?? null,
                        ]);
                    }
                }

                $createdUsers[] = $user;
            }

            DB::commit();

            return $createdUsers; // return all created users
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to upload users: ' . $e->getMessage(), [
                'data'  => $usersData,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // re-throw so the caller can handle it
        }
    }

    // public function delete(Role $role): void
    // {
    //     try {
    //         $role->delete();
    //     } catch (Exception $e) {
    //         Log::error("Failed to delete role {$role->id}: " . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString(),
    //         ]);
    //         throw $e;
    //     }
    // }
}
