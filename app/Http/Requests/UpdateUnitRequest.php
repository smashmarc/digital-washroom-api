<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('update', $this->route('unit'));
        return true;
    }

    public function rules(): array
    {
        $unit = $this->route('unit');
        $locationId = $this->location_id ?? $unit?->location_id;

        return [
            'location_id' => ['sometimes', 'exists:locations,id'],
            'name'        => [
                'required', 'string', 'max:255',
                Rule::unique('units', 'name')
                    ->where('location_id', $locationId)
                    ->ignore($unit?->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.exists' => 'The selected location does not exist.',
            'name.unique'        => 'A unit with this name already exists at the selected location.',
        ];
    }
}
