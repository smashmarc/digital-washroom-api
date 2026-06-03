<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AttachQuestionsToTemplateRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'questions'                   => 'required|array|min:1',
            'questions.*.question_id'     => 'required|integer|exists:questions,id',
            'questions.*.order'           => 'nullable|integer|min:0',
            'questions.*.weight_override' => 'nullable|integer|min:1|max:10',
        ];
    }
}
