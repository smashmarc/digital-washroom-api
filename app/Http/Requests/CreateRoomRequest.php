<?php

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('create', Room::class);
        return true; // adjust if you add policies
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:locations,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('rooms')
                    ->where(fn ($query) => $query->where('location_id', $this->location_id)),
            ],
            'records_to_show' => ['nullable', 'integer', 'min:1'],
        ];
    }


    public function messages(): array
    {
        return [
            'name.unique' => 'The room name already exists for this location.',
            'name.required' => 'Please enter a room name.',
            'location_id.required' => 'Please select a location.',
            'location_id.exists' => 'The selected location does not exist.',
            'records_to_show.integer' => 'Records to show must be a number.',
            'records_to_show.min' => 'Records to show must be at least 1.',
        ];
    }
}
