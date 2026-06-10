<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateEvaluationAnswerRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'value' => 'required|in:pass,fail,na',
            'notes' => 'nullable|string',
        ];
    }
}
