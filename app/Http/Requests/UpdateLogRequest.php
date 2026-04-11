<?php

namespace App\Http\Requests;

use App\Models\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('update', Log::class);
        return true; // adjust if needed
    }

    public function rules(): array
    {
        return [
            'room_id'   => ['sometimes', 'exists:rooms,id'],
            'user_id'   => ['sometimes', 'exists:users,id'],
            'note'      => ['sometimes', 'nullable', 'string'],
            'note_code' => ['sometimes', 'integer', 'in:0,1,2'],
            'logged_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
