<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // already authenticated via middleware
    }

    public function rules(): array
    {
        return [
            //'current_password' => ['required', 'string'],

            //uncomment if you want complicated password
            // 'new_password' => [
            //     'required',
            //     'string',
            //     'confirmed', // requires new_password_confirmation
            //     Password::min(12)              
            //         ->letters()
            //         ->mixedCase()
            //         ->numbers()
            //         ->symbols(), // 🔥 strong password
            // ],
            'new_password' => [
                'required',
                'string',
                'confirmed',
                'min:6'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required.',
            'new_password.required' => 'New password is required.',
            'new_password.confirmed' => 'Password confirmation does not match.',
        ];
    }

    //uncomment if needed current password
    // public function withValidator($validator)
    // {
    //     $validator->after(function ($validator) {
    //         $user = $this->user();

    //         if (!Hash::check($this->current_password, $user->password)) {
    //             $validator->errors()->add(
    //                 'current_password',
    //                 'Current password is incorrect.'
    //             );
    //         }
    //     });
    // }
}
