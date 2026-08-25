<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DepartmentResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at, 
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'location_id'=> $this->location_id,
            'username'=>$this->username,
            'is_active' => (bool) $this->is_active,
            'deactivated_at' => $this->deactivated_at,
            'location' => new LocationResource($this->whenLoaded('location')),
            'departments' => DepartmentResource::collection($this->whenLoaded('departments')),
        ];
    }
}
