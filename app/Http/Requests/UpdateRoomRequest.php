<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('update', Room::class);
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
