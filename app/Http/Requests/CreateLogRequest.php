<?php

namespace App\Http\Requests;

use App\Models\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('create', Log::class);
        return true; // you can use policies later if needed
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'note'    => ['nullable', 'string'],
            'note_code'=>['required', 'integer']
        ];
    }

    // Optionally, automatically attach the authenticated user ID
    // uncomment if needed
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $data['user_id'] = auth()->id(); // attach authenticated user
        return $data;
    }
}
