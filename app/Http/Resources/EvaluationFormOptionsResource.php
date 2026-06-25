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
            'departments'   => $this->resource['departments']->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'enable_unit_option' => (bool) $d->enable_unit_option]),
            'templates'     => $this->resource['templates']->map(fn($t) => [
                'id'             => $t->id,
                'name'           => $t->name,
                'description'    => $t->description,
                'pass_score'     => $t->pass_score,
                'department_ids' => $t->departments->pluck('id')->values(),
            ]),
            'units'         => $this->resource['units']->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'location_id' => $u->location_id]),
            'rooms'         => $this->resource['rooms']->map(fn($r) => ['id' => $r->id, 'name' => $r->name, 'location_id' => $r->location_id]),
            'locations'     => $this->resource['locations']->map(fn($l) => ['id' => $l->id, 'name' => $l->name]),
        ];
    }
}
