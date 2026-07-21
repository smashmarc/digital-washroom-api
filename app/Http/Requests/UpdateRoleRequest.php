<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'name'            => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($roleId)],
            'description'     => ['nullable', 'string', 'max:255'],
            'permissions'     => ['nullable', 'array'],
            'permissions.*'   => ['integer', 'exists:permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'          => 'Role name already exists.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ];
    }
}
