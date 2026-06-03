<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateQuestionCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $id = $this->route('question_category')?->id;
        return [
            'name'        => 'required|string|max:255',
            'slug'        => "nullable|string|max:255|unique:question_categories,slug,{$id}",
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ];
    }
}
