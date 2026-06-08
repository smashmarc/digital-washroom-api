<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class EvaluationAnswerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'evaluation_id'      => $this->evaluation_id,
            'criteria_id'        => $this->criteria_id,
            'criteria_snapshot'  => $this->criteria_snapshot,
            'criteria'           => new CriteriaResource($this->whenLoaded('criteria')),
            'value'              => $this->value,
            'notes'              => $this->notes,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
