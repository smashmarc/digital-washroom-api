<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location_id' => $this->location_id,
            'qr_code' => $this->qr_code,
            'logs' => LogResource::collection($this->whenLoaded('logs')),
            'last_cleaned' => $this->whenLoaded('lastCleanedLog', function () {
                return optional($this->lastCleanedLog)->created_at;
            }),
        ];
    }
}
