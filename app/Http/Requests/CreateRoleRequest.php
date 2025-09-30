<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('create', Role::class);
        return true; // adjust if you have policies
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id ?? null; // for update

        return [
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($roleId),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'], // optional permissions array
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ];
    }

}
