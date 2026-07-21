<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('update', $this->route('department'));
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore($this->route('department')?->id),
            ],
            'enable_unit_option' => ['boolean'],
        ];
    }
}
