<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // you can use policies later if needed
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'note'    => ['required', 'string'],
        ];
    }

    // Optionally, automatically attach the authenticated user ID
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $data['user_id'] = auth()->id(); // attach authenticated user
        return $data;
    }
}
