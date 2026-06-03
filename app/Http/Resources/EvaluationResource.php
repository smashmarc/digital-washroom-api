<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class EvaluationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'user_id'                => $this->user_id,
            'evaluation_template_id' => $this->evaluation_template_id,
            'user'                   => new UserResource($this->whenLoaded('user')),
            'template'               => new EvaluationTemplateResource($this->whenLoaded('template')),
            'evaluator_id'           => $this->evaluator_id,
            'evaluator'              => new UserResource($this->whenLoaded('evaluator')),
            'score'                  => $this->score,
            'pass_score'             => $this->pass_score,
            'result'                 => $this->result,
            'status'                 => $this->status,
            'fatal_failed'           => $this->fatal_failed,
            'overall_notes'          => $this->overall_notes,
            'submitted_at'           => $this->submitted_at,
            'answers'                => EvaluationAnswerResource::collection($this->whenLoaded('answers')),
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
