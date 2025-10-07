<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'operations' => [
                'locations_count' => $this['operations']['locations_count'],
                'rooms_count' => $this['operations']['rooms_count'],
                'rooms_cleaned_today' => $this['operations']['rooms_cleaned_today'],
                'rooms_needing_attention' => $this['operations']['rooms_needing_attention'],
                'cleaning_trend' => $this['operations']['cleaning_trend'],
                'logs_by_location' => $this['operations']['logs_by_location'],
                'recent_cleaning_logs' => LogResource::collection($this['operations']['recent_cleaning_logs']),
                'rooms_overdue' => RoomResource::collection($this['operations']['rooms_overdue']),
            ],
            'users' => [
                'total_users' => $this['users']['total_users'],
                'active_users_today' => $this['users']['active_users_today'],
                'logs_today' => $this['users']['logs_today'],
                'top_user_today' => $this['users']['top_user_today'] ? new UserResource($this['users']['top_user_today']) : null,
                'logs_per_user' => $this['users']['logs_per_user'],
                'user_activity_trend' => $this['users']['user_activity_trend'],
                'top_users_this_month' => $this['users']['top_users_this_month'],
                'latest_user_actions' => LogResource::collection($this['users']['latest_user_actions']),
            ],
        ];
    }
}
