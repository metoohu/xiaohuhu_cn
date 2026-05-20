<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\ForbiddenWordCategory;
use Illuminate\Validation\Validator;

/**
 * 调性类分类（level=tone）必须填写 replacement。
 */
trait ValidatesForbiddenWordReplacement
{
    /**
     * 校验调性类词条是否提供替换词。
     */
    protected function validateToneReplacement(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $categoryId = $this->input('category_id');
            if (! $categoryId) {
                return;
            }

            $category = ForbiddenWordCategory::query()->find($categoryId);
            if ($category === null) {
                return;
            }

            if ($category->level === ForbiddenWordCategory::LEVEL_TONE && blank($this->input('replacement'))) {
                $validator->errors()->add('replacement', '调性违规类词条必须填写替换词');
            }
        });
    }
}
