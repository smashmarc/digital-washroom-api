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
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.value'       => 'required|in:pass,fail,na',
            'answers.*.notes'       => 'nullable|string',
        ];
    }
}
