<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use App\Helpers\ApiResponse;
use App\Http\Resources\DashboardResource;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {


        // total locations
        $locationsCount = Location::count();

        // total rooms
        $roomsCount = Room::count();

        // rooms cleaned today (logs with note_code != 0)
        $roomsCleanedToday = Log::whereDate('created_at', today())
            ->where('note_code', '!=', 0)
            ->distinct('room_id')
            ->count('room_id');

        // rooms needing attention (no log in last X hours, example: 24h)
        $roomsNeedingAttention = Room::whereDoesntHave('logs', function ($q) {
            $q->where('note_code', '!=', 0)
                ->where('created_at', '>=', now()->subHours(24));
        })
            ->count();

        // cleaning trend (logs per day, last 7 days)
        $cleaningTrend = Log::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('note_code', '!=', 0)
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // logs by location
        $logsByLocation = Log::selectRaw('locations.name as location, COUNT(logs.id) as total')
            ->join('rooms', 'logs.room_id', '=', 'rooms.id')
            ->join('locations', 'rooms.location_id', '=', 'locations.id')
            ->groupBy('locations.name')
            ->get();


        // total users
        $totalUsers = User::count();

        // active users today (who created logs today)
        $activeUsersToday = User::whereHas('logs', function ($q) {
            $q->whereDate('created_at', today());
        })->count();

        // logs created today
        $logsToday = Log::whereDate('created_at', today())->count();

        // top active user today
        $topUserToday = User::select('users.id', 'users.name')
            ->join('logs', 'users.id', '=', 'logs.user_id')
            ->whereDate('logs.created_at', today())
            ->groupBy('users.id', 'users.name')
            ->orderByRaw('COUNT(logs.id) DESC')
            ->first();



        // logs per user (last 7 days)
        $logsPerUser = Log::selectRaw('users.name, COUNT(logs.id) as total')
            ->join('users', 'logs.user_id', '=', 'users.id')
            ->where('logs.created_at', '>=', now()->subDays(7))
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // user activity trend (active users per day)
        $userActivityTrend = Log::selectRaw('DATE(created_at) as date, COUNT(DISTINCT user_id) as total_users')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // top users by logs (this month)
        $topUsersThisMonth = User::select('users.id', 'users.name')
            ->join('logs', 'users.id', '=', 'logs.user_id')
            ->whereMonth('logs.created_at', now()->month)
            ->groupBy('users.id', 'users.name')
            ->selectRaw('COUNT(logs.id) as total_logs')
            ->orderByDesc('total_logs')
            ->take(10)
            ->get();

        // latest user actions (activity feed)
        $latestUserActions = Log::with(['room.location', 'user'])
            ->latest()
            ->take(10)
            ->get();

        // recent cleaning logs
        $recentCleaningLogs = Log::with(['room.location', 'user'])
            ->where('note_code', '!=', 0)
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
