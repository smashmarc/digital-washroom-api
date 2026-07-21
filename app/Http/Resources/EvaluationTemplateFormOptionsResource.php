<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class EvaluationTemplateFormOptionsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'criteria'    => CriteriaResource::collection($this->resource['criteria']),
            'departments' => $this->resource['departments']->map(fn($d) => ['id' => $d->id, 'name' => $d->name])->values(),
        ];
    }
}
