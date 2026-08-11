<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnnouncementService extends BaseService
{
    public function __construct(Announcement $announcement)
    {
        parent::__construct($announcement);
    }

    public function searchPaginatedList(array $params = [])
    {
        $params['searchableColumns'] = ['title', 'message'];
        return parent::list($params);
    }

    public function create(array $data): Announcement
    {
        DB::beginTransaction();
        try {
            $announcement = Announcement::create([
                'title'      => $data['title'],
                'message'    => $data['message'],
                'is_active'  => $data['is_active'] ?? true,
                'starts_at'  => $data['starts_at'] ?? null,
                'ends_at'    => $data['ends_at'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $announcement->roles()->sync($data['role_ids'] ?? []);
            $announcement->locations()->sync($data['location_ids'] ?? []);
            $announcement->departments()->sync($data['department_ids'] ?? []);

            DB::commit();
            return $announcement;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('AnnouncementService::create failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data]);
            throw $e;
        }
    }

    public function update(array $data, Announcement $announcement): Announcement
    {
        DB::beginTransaction();
        try {
            $announcement->title      = $data['title'] ?? $announcement->title;
            $announcement->message    = $data['message'] ?? $announcement->message;
            $announcement->is_active  = $data['is_active'] ?? $announcement->is_active;
            $announcement->starts_at  = $data['starts_at'] ?? null;
            $announcement->ends_at    = $data['ends_at'] ?? null;
            $announcement->save();

            if (isset($data['role_ids'])) {
                $announcement->roles()->sync($data['role_ids']);
            }
            if (isset($data['location_ids'])) {
                $announcement->locations()->sync($data['location_ids']);
            }
            if (isset($data['department_ids'])) {
                $announcement->departments()->sync($data['department_ids']);
            }

            DB::commit();
            return $announcement;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('AnnouncementService::update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data, 'announcement_id' => $announcement->id]);
            throw $e;
        }
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }

    public function getFormOptions(): array
    {
        return [
            'roles'       => Role::all(),
            'locations'   => Location::all(),
            'departments' => Department::orderBy('name')->get(),
        ];
    }

    public function getActiveForUser(User $user)
    {
        return Announcement::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) use ($user) {
                $q->where(function ($none) {
                    $none->whereDoesntHave('roles')
                        ->whereDoesntHave('locations')
                        ->whereDoesntHave('departments');
                })
                ->orWhereHas('roles', fn ($r) => $r->whereIn('roles.id', $user->roles->pluck('id')))
                ->orWhereHas('locations', fn ($l) => $l->where('locations.id', $user->location_id))
                ->orWhereHas('departments', fn ($d) => $d->whereIn('departments.id', $user->departments->pluck('id')));
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
