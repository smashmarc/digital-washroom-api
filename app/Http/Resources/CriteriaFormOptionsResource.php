<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class CriteriaFormOptionsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'categories' => CriteriaCategoryResource::collection($this->resource['categories']),
            'templates'  => EvaluationTemplateResource::collection($this->resource['templates']),
        ];
    }
}
