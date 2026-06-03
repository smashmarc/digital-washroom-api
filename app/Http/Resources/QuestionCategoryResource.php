<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class QuestionCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'is_active'   => $this->is_active,
            'questions_count' => $this->whenCounted('questions'),
            'questions'   => QuestionResource::collection($this->whenLoaded('questions')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
