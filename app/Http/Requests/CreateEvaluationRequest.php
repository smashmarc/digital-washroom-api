<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateEvaluationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'user_id'                => 'required|integer|exists:users,id',
            'evaluation_template_id' => 'required|integer|exists:evaluation_templates,id',
            'overall_notes'          => 'nullable|string',
            'unit_id'                => 'nullable|integer|exists:units,id',
            'room_name'              => 'nullable|string|max:255',
        ];
    }
}
