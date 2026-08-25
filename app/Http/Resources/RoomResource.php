<?php

namespace App\Http\Resources;


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
            'records_to_show' => $this->records_to_show,
            'logs' => LogResource::collection($this->whenLoaded('logs')),
            'last_cleaned' => LogResource::collection($this->whenLoaded('lastCleanedLog')),
            'location'=>new LocationResource($this->whenLoaded('location'))
        ];
    }
}
