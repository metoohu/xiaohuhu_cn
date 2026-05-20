<?php

namespace App\Services\ForbiddenWord;

/**
 * 将物理字段名映射为 title / body / other 角色。
 */
class ForbiddenWordFieldMapper
{
    public function roleForField(string $fieldKey): string
    {
        $roles = config('forbidden_words.field_roles', []);

        foreach ($roles as $role => $fields) {
            if (in_array($fieldKey, $fields, true)) {
                return $role;
            }
        }

        return 'other';
    }
}
