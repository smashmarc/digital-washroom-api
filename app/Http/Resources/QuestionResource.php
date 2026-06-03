<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class QuestionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'question_category_id' => $this->question_category_id,
            'category'             => new QuestionCategoryResource($this->whenLoaded('category')),
            'text'                 => $this->text,
            'type'                 => $this->type,
            'weight'               => $this->weight,
            'is_fatal'             => $this->is_fatal,
            'is_active'            => $this->is_active,
            'pivot'                => $this->when(
                $this->relationLoaded('pivot') || isset($this->pivot),
                fn() => [
                    'order'          => $this->pivot?->order,
                    'weight_override' => $this->pivot?->weight_override,
                ]
            ),
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
