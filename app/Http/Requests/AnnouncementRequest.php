<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'             => 'required|string|max:255',
            'message'           => 'required|string',
            'is_active'         => 'nullable|boolean',
            'starts_at'         => 'nullable|date',
            'ends_at'           => 'nullable|date|after_or_equal:starts_at',
            'role_ids'          => 'nullable|array',
            'role_ids.*'        => 'integer|exists:roles,id',
            'location_ids'      => 'nullable|array',
            'location_ids.*'    => 'integer|exists:locations,id',
            'department_ids'    => 'nullable|array',
            'department_ids.*'  => 'integer|exists:departments,id',
        ];
    }
}
