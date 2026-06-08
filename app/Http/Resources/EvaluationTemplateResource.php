<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class EvaluationTemplateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'pass_score'  => $this->pass_score,
            'is_active'   => $this->is_active,
            'created_by'  => $this->created_by,
            'creator'     => new UserResource($this->whenLoaded('creator')),
            'criteria'        => CriteriaResource::collection($this->whenLoaded('criteria')),
            'criteria_count'  => $this->whenCounted('criteria'),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
