<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class AttachePermissionsToRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // you can check policy if needed, e.g.
         return $this->user()->can('update', Role::class);     
    }

    public function rules(): array
    {
        return [
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.required'   => 'You must provide at least one permission.',
            'permissions.array'      => 'Permissions must be an array.',
            'permissions.*.string'   => 'Each permission must be a string.',
            'permissions.*.exists'   => 'One or more permissions are invalid.',
        ];
    }
}
