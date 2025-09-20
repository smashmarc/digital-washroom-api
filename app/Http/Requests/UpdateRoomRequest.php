<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'The room name already exists for this location.',
            'name.required' => 'Please enter a room name.',
            'location_id.exists' => 'The selected location does not exist.',
        ];
    }
}
