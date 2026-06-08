<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateCriteriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'criteria_category_id' => 'required|integer|exists:criteria_categories,id',
            'text'                 => 'required|string',
            'is_active'            => 'nullable|boolean',
            'template_ids'         => 'nullable|array',
            'template_ids.*'       => 'integer|exists:evaluation_templates,id',
        ];
    }
}
