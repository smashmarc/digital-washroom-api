<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateEvaluationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'user_assignment_id' => 'required|integer|exists:user_assignments,id',
            'overall_notes'      => 'nullable|string',
        ];
    }
}
