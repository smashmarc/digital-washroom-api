<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateUserAssignmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'user_id'                => 'required|integer|exists:users,id',
            'evaluation_template_id' => 'required|integer|exists:evaluation_templates,id',
            'due_date'               => 'nullable|date|after_or_equal:today',
            'notes'                  => 'nullable|string',
        ];
    }
}
