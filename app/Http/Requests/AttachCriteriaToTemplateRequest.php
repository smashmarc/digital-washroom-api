<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AttachCriteriaToTemplateRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'criteria'              => 'required|array|min:1',
            'criteria.*.criteria_id' => 'required|integer|exists:criteria,id',
            'criteria.*.order'      => 'nullable|integer|min:0',
        ];
    }
}
