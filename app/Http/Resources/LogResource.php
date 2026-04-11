<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        //note code 0=not cleaned, 1=partially cleaned, 2=fully cleaned
        $statusMap = [
        0 => 'Not Cleaned',
        1 => 'Partially Cleaned',
        2 => 'Fully Cleaned',
    ];
       return [
            'id'         => $this->id,
            'room_id'    => $this->room_id,
            'user_id'    => $this->user_id,
            'note'       => $this->note,
            'room'       => new RoomResource($this->whenLoaded('room')),
            'user'       => new UserResource($this->whenLoaded('user')),
            'logged_at'   => $this->logged_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'status_code' => $this->note_code,
            'status'      => $statusMap[$this->note_code] ?? 'Unknown',
        ];
    }
}
