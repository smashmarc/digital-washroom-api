<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateUserAssignmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'user_id'                => 'sometimes|integer|exists:users,id',
            'evaluation_template_id' => 'sometimes|integer|exists:evaluation_templates,id',
            'due_date'               => 'nullable|date',
            'status'                 => 'nullable|in:pending,in_progress,completed',
            'notes'                  => 'nullable|string',
        ];
    }
}
