<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateAndSubmitEvaluationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'user_id'                => 'required|integer|exists:users,id',
            'department_id'          => 'required|integer|exists:departments,id',
            'evaluation_template_id' => 'required|integer|exists:evaluation_templates,id',
            'overall_notes'          => 'nullable|string',
            'location_id'            => 'required|integer|exists:locations,id',
            'unit_id'                => 'nullable|integer|exists:units,id',
            'room_name'              => 'nullable|string|max:255',
            'answers'                => 'required|array|min:1',
            'answers.*.criteria_id'  => 'required|integer|exists:criteria,id',
            'answers.*.value'        => 'required|in:pass,fail,na',
            'answers.*.notes'        => 'nullable|string',
        ];
    }
}
