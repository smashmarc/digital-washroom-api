<?php

namespace App\Http\Requests;

use App\Models\Location;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('create', Location::class);
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:locations,name',
            'address' => 'required|string',
            'logo'    => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            
            'name.unique'=>'Location Already exists.'
        ];
    }

}
