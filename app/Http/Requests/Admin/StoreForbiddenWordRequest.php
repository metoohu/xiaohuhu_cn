<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesForbiddenWordReplacement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * 新增违禁词条表单校验。
 */
class StoreForbiddenWordRequest extends FormRequest
{
    use ValidatesForbiddenWordReplacement;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:forbidden_word_categories,id',
            'word' => 'required|string|max:100',
            'match_type' => 'required|in:exact,fuzzy',
            'replacement' => 'nullable|string|max:100',
            'is_enabled' => 'sometimes|boolean',
            'remark' => 'nullable|string|max:255',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateToneReplacement($validator);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => '分类',
            'word' => '违禁词',
            'match_type' => '匹配方式',
            'replacement' => '替换词',
            'is_enabled' => '启用状态',
            'remark' => '备注',
        ];
    }

    /**
     * 规范化布尔字段。
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_enabled')) {
            $this->merge([
                'is_enabled' => $this->boolean('is_enabled'),
            ]);
        }
    }
}
