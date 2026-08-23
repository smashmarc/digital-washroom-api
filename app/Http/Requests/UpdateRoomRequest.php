<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['sometimes', 'exists:locations,id'],
            'name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('rooms')
                    ->ignore($this->room) // assumes route model binding {room}
                    ->where(fn ($query) => $query->where('location_id', $this->location_id ?? $this->room->location_id)),
            ],
            'records_to_show' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'The room name already exists for this location.',
            'name.required' => 'Please enter a room name.',
            'location_id.exists' => 'The selected location does not exist.',
            'records_to_show.integer' => 'Records to show must be a number.',
            'records_to_show.min' => 'Records to show must be at least 1.',
        ];
    }
}
