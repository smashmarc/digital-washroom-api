<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class EvaluationTemplateFormOptionsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'questions' => QuestionResource::collection($this->resource['questions']),
        ];
    }
}
