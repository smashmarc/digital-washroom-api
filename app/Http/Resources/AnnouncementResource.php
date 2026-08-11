<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'message'     => $this->message,
            'is_active'   => (bool) $this->is_active,
            'starts_at'   => $this->starts_at,
            'ends_at'     => $this->ends_at,
            'created_by'  => $this->created_by,
            'creator'     => new UserResource($this->whenLoaded('creator')),
            'roles'       => RoleResource::collection($this->whenLoaded('roles')),
            'locations'   => LocationResource::collection($this->whenLoaded('locations')),
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
