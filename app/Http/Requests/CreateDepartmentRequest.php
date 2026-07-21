<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('create', Department::class);
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255', 'unique:departments,name'],
            'enable_unit_option' => ['boolean'],
        ];
    }
}
