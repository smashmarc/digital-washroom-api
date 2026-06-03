<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateQuestionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'question_category_id' => 'required|integer|exists:question_categories,id',
            'text'                 => 'required|string',
            'type'                 => 'nullable|in:pass_fail,text,scale',
            'weight'               => 'nullable|integer|min:1|max:10',
            'is_fatal'             => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
        ];
    }
}
