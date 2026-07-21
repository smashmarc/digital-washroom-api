<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class UserAssignmentFormOptionsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'users'     => UserResource::collection($this->resource['users']),
            'templates' => EvaluationTemplateResource::collection($this->resource['templates']),
            'statuses'  => $this->resource['statuses'],
        ];
    }
}
