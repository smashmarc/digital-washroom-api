<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class EvaluationAnswerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'evaluation_id' => $this->evaluation_id,
            'question_id'   => $this->question_id,
            'question'      => new QuestionResource($this->whenLoaded('question')),
            'value'         => $this->value,
            'notes'         => $this->notes,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
