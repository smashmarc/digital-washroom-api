<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class UserAssignmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'user_id'                => $this->user_id,
            'user'                   => new UserResource($this->whenLoaded('user')),
            'evaluation_template_id' => $this->evaluation_template_id,
            'template'               => new EvaluationTemplateResource($this->whenLoaded('template')),
            'assigned_by'            => $this->assigned_by,
            'assigner'               => new UserResource($this->whenLoaded('assigner')),
            'due_date'               => $this->due_date?->toDateString(),
            'status'                 => $this->status,
            'notes'                  => $this->notes,
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
