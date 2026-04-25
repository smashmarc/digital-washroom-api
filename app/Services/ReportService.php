<?php

namespace App\Services;

use App\Models\Log as LogModel;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // ── Shared filter helpers ────────────────────────────────────────

    private function applyDateRange($query, array $params, string $column = 'created_at'): void
    {
        if (!empty($params['date_from'])) {
            $query->whereDate($column, '>=', $params['date_from']);
        }
        if (!empty($params['date_to'])) {
            $query->whereDate($column, '<=', $params['date_to']);
        }
    }

    // ── 1. Cleaning Activity ─────────────────────────────────────────

    public function cleaningActivity(array $params = [])
    {
        $query = LogModel::with(['room.location', 'user'])
            ->select('logs.*');

        $this->applyDateRange($query, $params, 'logged_at');

        if (!empty($params['location_id'])) {
            $query->whereHas('room.location', fn($q) =>
                $q->where('location_id', $params['location_id'])
            );
        }

        if (!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        if (isset($params['status_code']) && $params['status_code'] !== '') {
            $query->where('note_code', $params['status_code']);
        }

        return $query->orderBy('logged_at', 'desc')
                     ->paginate($params['per_page'] ?? 25);
    }

    public function cleaningActivityExport(array $params = [])
    {
        return $this->cleaningActivity(array_merge($params, ['per_page' => PHP_INT_MAX]))
                    ->getCollection();
    }

    // ── 2. Room Status ───────────────────────────────────────────────

    public function roomStatus(array $params = [])
    {
        $query = Room::with(['location', 'logs' => fn($q) => $q->latest('logged_at')->limit(1)->with('user')])
            ->select('rooms.*');

        if (!empty($params['location_id'])) {
            $query->where('location_id', $params['location_id']);
        }

        if (isset($params['status_code']) && $params['status_code'] !== '') {
            $query->whereHas('logs', fn($q) =>
                $q->where('note_code', $params['status_code'])
                  ->whereIn('id', fn($sub) =>
                      $sub->select(DB::raw('MAX(id)'))->from('logs')->groupBy('room_id')
                  )
            );
        }

        return $query->orderBy('location_id')
                     ->orderBy('name')
                     ->paginate($params['per_page'] ?? 25);
    }

    public function roomStatusExport(array $params = [])
    {
        return $this->roomStatus(array_merge($params, ['per_page' => PHP_INT_MAX]))
                    ->getCollection();
    }

    // ── 3. User Performance ──────────────────────────────────────────

    public function userPerformance(array $params = [])
    {
        $query = User::select('users.*')
            ->selectSub(
                fn($q) => $q->from('logs')
                    ->whereColumn('user_id', 'users.id')
                    ->when(!empty($params['date_from']), fn($q) => $q->whereDate('logged_at', '>=', $params['date_from']))
                    ->when(!empty($params['date_to']),   fn($q) => $q->whereDate('logged_at', '<=', $params['date_to']))
                    ->selectRaw('COUNT(*)'),
                'total_logs'
            )
            ->selectSub(
                fn($q) => $q->from('logs')
                    ->whereColumn('user_id', 'users.id')
                    ->where('note_code', 2)
                    ->when(!empty($params['date_from']), fn($q) => $q->whereDate('logged_at', '>=', $params['date_from']))
                    ->when(!empty($params['date_to']),   fn($q) => $q->whereDate('logged_at', '<=', $params['date_to']))
                    ->selectRaw('COUNT(*)'),
                'fully_cleaned'
            )
            ->selectSub(
                fn($q) => $q->from('logs')
                    ->whereColumn('user_id', 'users.id')
                    ->where('note_code', 1)
                    ->when(!empty($params['date_from']), fn($q) => $q->whereDate('logged_at', '>=', $params['date_from']))
                    ->when(!empty($params['date_to']),   fn($q) => $q->whereDate('logged_at', '<=', $params['date_to']))
                    ->selectRaw('COUNT(*)'),
                'partially_cleaned'
            )
            ->selectSub(
                fn($q) => $q->from('logs')
                    ->whereColumn('user_id', 'users.id')
                    ->where('note_code', 0)
                    ->when(!empty($params['date_from']), fn($q) => $q->whereDate('logged_at', '>=', $params['date_from']))
                    ->when(!empty($params['date_to']),   fn($q) => $q->whereDate('logged_at', '<=', $params['date_to']))
                    ->selectRaw('COUNT(*)'),
                'not_cleaned'
            )
            ->selectSub(
                fn($q) => $q->from('logs')
                    ->whereColumn('user_id', 'users.id')
                    ->selectRaw('MAX(logged_at)'),
                'last_active'
            )
            ->having('total_logs', '>', 0);

        if (!empty($params['user_id'])) {
            $query->where('users.id', $params['user_id']);
        }

        return $query->orderByDesc('total_logs')
                     ->paginate($params['per_page'] ?? 25);
    }

    public function userPerformanceExport(array $params = [])
    {
        return $this->userPerformance(array_merge($params, ['per_page' => PHP_INT_MAX]))
                    ->getCollection();
    }

    // ── 4. Location Summary ──────────────────────────────────────────

    public function locationSummary(array $params = [])
    {
        $query = Location::select('locations.*')
            ->selectSub(
                fn($q) => $q->from('rooms')
                    ->whereColumn('location_id', 'locations.id')
                    ->selectRaw('COUNT(*)'),
                'total_rooms'
            )
            ->selectSub(
                fn($q) => $q->from('logs')
                    ->join('rooms', 'logs.room_id', '=', 'rooms.id')
                    ->whereColumn('rooms.location_id', 'locations.id')
                    ->when(!empty($params['date_from']), fn($q) => $q->whereDate('logs.logged_at', '>=', $params['date_from']))
                    ->when(!empty($params['date_to']),   fn($q) => $q->whereDate('logs.logged_at', '<=', $params['date_to']))
                    ->selectRaw('COUNT(*)'),
                'total_logs'
            )
            ->selectSub(
                fn($q) => $q->from('logs')
                    ->join('rooms', 'logs.room_id', '=', 'rooms.id')
                    ->whereColumn('rooms.location_id', 'locations.id')
                    ->where('logs.note_code', 2)
                    ->when(!empty($params['date_from']), fn($q) => $q->whereDate('logs.logged_at', '>=', $params['date_from']))
                    ->when(!empty($params['date_to']),   fn($q) => $q->whereDate('logs.logged_at', '<=', $params['date_to']))
                    ->selectRaw('COUNT(*)'),
                'fully_cleaned'
            )
            ->selectSub(
                fn($q) => $q->from('logs')
                    ->join('rooms', 'logs.room_id', '=', 'rooms.id')
                    ->whereColumn('rooms.location_id', 'locations.id')
                    ->where('logs.note_code', 1)
                    ->when(!empty($params['date_from']), fn($q) => $q->whereDate('logs.logged_at', '>=', $params['date_from']))
                    ->when(!empty($params['date_to']),   fn($q) => $q->whereDate('logs.logged_at', '<=', $params['date_to']))
                    ->selectRaw('COUNT(*)'),
                'partially_cleaned'
            )
            ->selectSub(
                fn($q) => $q->from('logs')
                    ->join('rooms', 'logs.room_id', '=', 'rooms.id')
                    ->whereColumn('rooms.location_id', 'locations.id')
                    ->where('logs.note_code', 0)
                    ->when(!empty($params['date_from']), fn($q) => $q->whereDate('logs.logged_at', '>=', $params['date_from']))
                    ->when(!empty($params['date_to']),   fn($q) => $q->whereDate('logs.logged_at', '<=', $params['date_to']))
                    ->selectRaw('COUNT(*)'),
                'not_cleaned'
            );

        if (!empty($params['location_id'])) {
            $query->where('locations.id', $params['location_id']);
        }

        return $query->orderBy('name')
                     ->paginate($params['per_page'] ?? 25);
    }

    public function locationSummaryExport(array $params = [])
    {
        return $this->locationSummary(array_merge($params, ['per_page' => PHP_INT_MAX]))
                    ->getCollection();
    }
}