<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 后台违禁词实时扫描请求校验。
 */
class ScanForbiddenContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fields' => ['required', 'array'],
            'fields.*' => ['nullable', 'string'],
            'context' => ['required', 'string', 'in:'.implode(',', config('forbidden_words.contexts', []))],
        ];
    }
}
