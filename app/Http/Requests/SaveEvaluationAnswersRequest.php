<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class SaveEvaluationAnswersRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'answers'               => 'required|array|min:1',
            'answers.*.criteria_id' => 'required|integer|exists:criteria,id',
            'answers.*.value'       => 'required|in:pass,fail,na',
            'answers.*.notes'       => 'nullable|string',
            'overall_notes'         => 'nullable|string',
        ];
    }
}
