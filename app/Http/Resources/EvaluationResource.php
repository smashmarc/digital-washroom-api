<?php
namespace App\Http\Resources;
use App\Models\EvaluationDepartment;
use Illuminate\Http\Resources\Json\JsonResource;
class EvaluationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'location_id' => $this->location_id,
            'location'    => $this->whenLoaded('location', fn() => [
                'id'   => $this->location->id,
                'name' => $this->location->name,
            ]),
            'unit_id'     => $this->unit_id,
            'unit'        => $this->whenLoaded('unit', fn() => [
                'id'   => $this->unit->id,
                'name' => $this->unit->name,
            ]),
            'room_name'   => $this->room_name,
            'departments' => $this->whenLoaded('evaluationDepartments', fn() =>
                $this->evaluationDepartments->map(fn(EvaluationDepartment $ed) => [
                    'id'   => $ed->department_id,
                    'name' => $ed->name,
                ])
            ),
            'evaluation_template_id' => $this->evaluation_template_id,
            'user'                   => new UserResource($this->whenLoaded('user')),
            'template'               => new EvaluationTemplateResource($this->whenLoaded('template')),
            'evaluator_id'           => $this->evaluator_id,
            'evaluator'              => new UserResource($this->whenLoaded('evaluator')),
            'updated_by'             => $this->updated_by,
            'updated_by_user'        => $this->whenLoaded('updatedBy', fn() => [
                'id'   => $this->updatedBy->id,
                'name' => $this->updatedBy->name,
            ]),
            'score'                  => $this->score,
            'pass_score'             => $this->pass_score,
            'result'                 => $this->result,
            'status'                 => $this->status,
            'overall_notes'          => $this->overall_notes,
            'submitted_at'           => $this->submitted_at,
            'answers'                => EvaluationAnswerResource::collection($this->whenLoaded('answers')),
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
