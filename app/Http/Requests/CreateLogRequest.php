<?php

namespace App\Http\Requests;

use App\Models\Log;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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

    $user = Auth::guard('entra')->user();

    if (!$user) {
        throw new \RuntimeException('Unauthenticated user cannot create log.');
    }

    $data['user_id'] = $user->id;

    return $data;

    }
}
