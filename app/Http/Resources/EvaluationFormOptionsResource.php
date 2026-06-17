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
            'units'         => $this->resource['units']->map(fn($u) => ['id' => $u->id, 'name' => $u->name]),
            'rooms'         => $this->resource['rooms']->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'location_id' => $r->location_id]),
            'locations'     => $this->resource['locations']->map(fn($l) => ['id' => $l->id, 'name' => $l->name]),
        ];
    }
}
