<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // adjust if needed
    }

    public function rules(): array
    {
        return [
            'room_id' => ['sometimes', 'exists:rooms,id'],
            'user_id' => ['sometimes', 'exists:users,id'],
            'note'    => ['sometimes', 'string'],
        ];
    }
}
