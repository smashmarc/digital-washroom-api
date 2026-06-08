<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class EvaluationFormOptionsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'answer_values' => $this->resource['answer_values'],
            'statuses'      => $this->resource['statuses'],
            'users'         => UserResource::collection($this->resource['users']),
            'templates'     => EvaluationTemplateResource::collection($this->resource['templates']),
        ];
    }
}
