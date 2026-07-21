<?php

namespace App\Http\Requests;

use App\Models\Location;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('update', new Location());
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('locations', 'name')->ignore($this->route('location')->id),
            ],
            'branding_title'  => 'nullable|string|max:150',
            'address'         => 'required|string',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'remove_logo'     => 'nullable|boolean',

        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Location Already exists.'
        ];
    }
}
