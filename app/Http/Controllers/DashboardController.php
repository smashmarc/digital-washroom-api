<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use App\Helpers\ApiResponse;
use App\Http\Resources\DashboardResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = Carbon::parse($request->query('date_from'))->utc();
        $dateTo   = Carbon::parse($request->query('date_to'))->utc();

        $locationsCount = Location::count();

        // total rooms
        $roomsCount = Room::count();

        // rooms cleaned today
        $roomsCleanedToday = Room::whereHas('logs', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('logged_at', [$dateFrom, $dateTo])
                ->where('note_code', '!=', 0);
        })->count();

        // rooms needing attention (no log in last 24h)
        $roomsNeedingAttention = Room::whereDoesntHave('logs', function ($q) {
            $q->where('note_code', '!=', 0)
                ->where('logged_at', '>=', now()->subHours(24));
        })->count();

        // total users
        $totalUsers = User::count();

        // active users today
        $activeUsersToday = User::whereHas('logs', function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('logged_at', [$dateFrom, $dateTo]);
        })->count();

        // logs created today
        $logsToday = Log::whereBetween('logged_at', [$dateFrom, $dateTo])->count();

        // top active user today
        $topUserToday = User::withCount(['logs as logs_today' => function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('logged_at', [$dateFrom, $dateTo]);
        }])
            ->orderByDesc('logs_today')
            ->first(['id', 'name', 'username']);

        $logsPerUser = [];

        // $cleaningTrend = Log::query()
        //     ->selectRaw('DATE(logged_at) as date, COUNT(*) as total')
        //     ->where('note_code', '!=', 0)
        //     ->where('logged_at', '>=', now()->subDays(7))
        //     ->groupBy('date')
        //     ->orderBy('date')
        //     ->get();

        // $logsByLocation = Location::withCount(['rooms as total' => function ($q) {
        //     $q->join('logs', 'rooms.id', '=', 'logs.room_id');
        // }])->get(['id', 'name']);

        // $userActivityTrend = Log::query()
        //     ->selectRaw('DATE(logged_at) as date, COUNT(DISTINCT user_id) as total_users')
        //     ->where('logged_at', '>=', now()->subDays(7))
        //     ->groupBy('date')
        //     ->orderBy('date')
        //     ->get();

        // $topUsersThisMonth = User::withCount(['logs as total_logs' => function ($q) {
        //     $q->whereMonth('logged_at', now()->month)
        //         ->whereYear('logged_at', now()->year);
        // }])
        //     ->orderByDesc('total_logs')
        //     ->take(10)
        //     ->get(['id', 'name']);

        // $latestUserActions = Log::with(['room.location', 'user'])
        //     ->latest('logged_at')
        //     ->take(10)
        //     ->get();

        // $recentCleaningLogs = Log::with(['room.location', 'user'])
        //     ->where('note_code', '!=', 4)
        //     ->latest('logged_at')
        //     ->take(10)
        //     ->get();

        // $roomsOverdue = Room::whereDoesntHave('logs', function ($q) {
        //     $q->where('note_code', '!=', 0)
        //         ->where('logged_at', '>=', now()->subHours(24));
        // })->with('location')->get();

        $items = [
            'operations' => [
                'locations_count' => $locationsCount,
                'rooms_count' => $roomsCount,
                'rooms_cleaned_today' => $roomsCleanedToday,
                'rooms_needing_attention' => $roomsNeedingAttention,
                // 'cleaning_trend' => $cleaningTrend,
                // 'logs_by_location' => $logsByLocation,
                // 'recent_cleaning_logs' => $recentCleaningLogs,
                // 'rooms_overdue' => $roomsOverdue,
            ],
            'users' => [
                'total_users' => $totalUsers,
                'active_users_today' => $activeUsersToday,
                'logs_today' => $logsToday,
                'top_user_today' => $topUserToday,
                'logs_per_user' => $logsPerUser,
                // 'user_activity_trend' => $userActivityTrend,
                // 'top_users_this_month' => $topUsersThisMonth,
                // 'latest_user_actions' => $latestUserActions,
            ]
        ];

        return ApiResponse::success(
            'dashboard data fetched.',
            new DashboardResource($items),
            200
        );
    }
}
