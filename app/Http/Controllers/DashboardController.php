<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use App\Helpers\ApiResponse;
use App\Http\Resources\DashboardResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index()
    {
     


        $locationsCount = Location::count();

        // total rooms
        $roomsCount = Room::count();

        // rooms cleaned today (logs with note_code != 0)
        $roomsCleanedToday = Room::whereHas('logs', function ($q) {
            $q->whereDate('created_at', today())
                ->where('note_code', '!=', 0);
        })
            ->count();

        // rooms needing attention (no log in last 24h)
        $roomsNeedingAttention = Room::whereDoesntHave('logs', function ($q) {
            $q->where('note_code', '!=', 0)
                ->where('created_at', '>=', now()->subHours(24));
        })
            ->count();

        // cleaning trend (logs per day, last 7 days)
        $cleaningTrend = Log::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('note_code', '!=', 0)
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // logs by location (using relationships instead of raw joins)
        $logsByLocation = Location::withCount(['rooms as total' => function ($q) {
            $q->join('logs', 'rooms.id', '=', 'logs.room_id');
        }])
            ->get(['id', 'name']);

        // total users
        $totalUsers = User::count();

        // active users today (who created logs today)
        $activeUsersToday = User::whereHas('logs', function ($q) {
            $q->whereDate('created_at', today());
        })
            ->count();

        // logs created today
        $logsToday = Log::whereDate('created_at', today())->count();

        // top active user today
        $topUserToday = User::withCount(['logs as logs_today' => function ($q) {
            $q->whereDate('created_at', today());
        }])
            ->orderByDesc('logs_today')
            ->first(['id', 'name', 'username']);

        // logs per user (last 7 days, top 5)
        // uncomment if needed
        // $logsPerUser = User::withCount(['logs as total' => function ($q) {
        //     $q->where('created_at', '>=', now()->subDays(7));
        // }])
        //     ->orderByDesc('total')
        //     ->take(5)
        //     ->get(['id', 'name']);
        $logsPerUser=[];

        // user activity trend (active users per day)
        $userActivityTrend = Log::query()
            ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT user_id) as total_users')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // top users by logs (this month)
        $topUsersThisMonth = User::withCount(['logs as total_logs' => function ($q) {
            $q->whereMonth('created_at', now()->month);
        }])
            ->orderByDesc('total_logs')
            ->take(10)
            ->get(['id', 'name']);

        // latest user actions (activity feed)
        $latestUserActions = Log::with(['room.location', 'user'])
            ->latest()
            ->take(10)
            ->get();

        // recent cleaning logs
        $recentCleaningLogs = Log::with(['room.location', 'user'])
            ->where('note_code', '!=', 4)
            ->latest()
            ->take(10)
            ->get();

        // rooms overdue (no cleaning log in last 24h)
        $roomsOverdue = Room::whereDoesntHave('logs', function ($q) {
            $q->where('note_code', '!=', 0)
                ->where('created_at', '>=', now()->subHours(24));
        })
            ->with('location')
            ->get();



        $items = [
            'operations' => [
                'locations_count' => $locationsCount,
                'rooms_count' => $roomsCount,
                'rooms_cleaned_today' => $roomsCleanedToday,
                'rooms_needing_attention' => $roomsNeedingAttention,
                'cleaning_trend' => $cleaningTrend,
                'logs_by_location' => $logsByLocation,
                'recent_cleaning_logs' => $recentCleaningLogs,
                'rooms_overdue' => $roomsOverdue,
            ],
            'users' => [
                'total_users' => $totalUsers,
                'active_users_today' => $activeUsersToday,
                'logs_today' => $logsToday,
                'top_user_today' => $topUserToday,
                'logs_per_user' => $logsPerUser,
                'user_activity_trend' => $userActivityTrend,
                'top_users_this_month' => $topUsersThisMonth,
                'latest_user_actions' => $latestUserActions,
            ]
        ];

        return ApiResponse::success(
            'dashboard data fetched.',
            new DashboardResource($items),
            200
        );
    }
}
