<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class CriteriaCategoryFormOptionsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'categories' => CriteriaCategoryResource::collection($this->resource['categories']),
        ];
    }
}
