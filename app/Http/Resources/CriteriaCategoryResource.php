<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class CriteriaCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'is_active'      => $this->is_active,
            'criteria_count' => $this->whenCounted('criteria'),
            'criteria'       => CriteriaResource::collection($this->whenLoaded('criteria')),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
