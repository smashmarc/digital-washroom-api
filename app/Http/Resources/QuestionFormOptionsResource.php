<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class QuestionFormOptionsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'categories' => QuestionCategoryResource::collection($this->resource['categories']),
            'types'      => $this->resource['types'],
        ];
    }
}
