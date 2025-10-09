<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        //return $this->user()->can('update', User::class);
        // Delegate to policy with the actual user being updated
        //route model binding made possible getting the model instance
        //return $this->user()->can('update', $this->route('user'));
        Gate::authorize('update', User::class);
        return true;
    }

    public function rules(): array
    {

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'roles'    => ['nullable', 'array'], // roles array
            'roles.*'  => ['integer', 'exists:roles,id'], // each role must exist in roles table
            'location_id' => ['nullable', 'exists:locations,id'],
            'username' => [
                'required',
                'string',
                'max:32',
                Rule::unique('users', 'username')->ignore($this->user->id),
            ],
        ];
    }

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
