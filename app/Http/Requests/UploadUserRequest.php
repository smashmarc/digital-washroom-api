<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Gate is handled in the controller
    }

    public function rules(): array
    {
        return [
            'users'            => ['required', 'array', 'min:1', $this->noDuplicatesRule()],
            'users.*.name'     => ['required', 'string', 'max:255'],
            'users.*.email'    => ['required', 'email', Rule::unique('users', 'email')],
            'users.*.username' => ['required', 'string', Rule::unique('users', 'username')],
            'users.*.password' => ['nullable', 'string', 'min:6'],
            'users.*.roles'    => ['nullable', 'array'],          
            'users.*.location' => ['nullable', 'string'],
            'default_password'=>  ['nullable', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'users.*.email.unique'    => 'email has already been taken.',
            'users.*.username.unique' => 'username has already been taken.',
            'users.*.name.required'   => 'name is required.',
            'users.*.email.required'  => 'email is required.',
            'users.*.email.email'     => 'email must be a valid email address.',
            'users.*.username.required' => 'username is required.',
        ];
    }

    private function noDuplicatesRule(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $usernames = array_column($value, 'username');
            $emails    = array_column($value, 'email');
            $seenUsernames = [];
            $seenEmails    = [];

            foreach ($usernames as $index => $username) {
                if (in_array($username, $seenUsernames)) {
                    $fail("Duplicate username '{$username}' found at row " . ($index + 1) . ".");
                }
                $seenUsernames[] = $username;
            }

            foreach ($emails as $index => $email) {
                if (in_array($email, $seenEmails)) {
                    $fail("Duplicate email '{$email}' found at row " . ($index + 1) . ".");
                }
                $seenEmails[] = $email;
            }
        };
    }
}