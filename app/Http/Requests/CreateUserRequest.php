<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Constants\PermissionConstant;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // return $this->user()->can('create', User::class);
        Gate::authorize('create', User::class);
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'roles'    => ['nullable', 'array'], // roles array
            'roles.*'  => ['integer', 'exists:roles,id'], // each role must exist in roles table
            'location_id' => ['nullable', 'exists:locations,id'],
            'username' => ['required', 'string', 'max:32', 'unique:users,username']
        ];
    }

    /**
     * Optional: customize validation messages
     */
    public function messages(): array
    {
        return [
            'roles.*.exists' => 'One or more selected roles are invalid.',
            'location_id.exists' => 'location is invalid.',
            'email.unique'=>'Email already exists.',
            'username.unique'=>'Username already exists.'
        ];
    }
}
