<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DepartmentResource;

class UserFormOptionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'roles'       => RoleResource::collection($this['roles']),
            'locations'   => LocationResource::collection($this['locations']),
            'departments' => DepartmentResource::collection($this['departments']),
        ];
        
    }
}
