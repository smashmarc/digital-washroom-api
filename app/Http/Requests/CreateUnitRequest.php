<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('create', Unit::class);
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'exists:locations,id'],
            'name'        => [
                'required', 'string', 'max:255',
                Rule::unique('units', 'name')->where('location_id', $this->location_id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.required' => 'Please select a location.',
            'location_id.exists'   => 'The selected location does not exist.',
            'name.unique'          => 'A unit with this name already exists at the selected location.',
        ];
    }
}
