<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource
;
class QuestionCategoryFormOptionsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'categories' => QuestionCategoryResource::collection($this->resource['categories']),
        ];
    }
}
