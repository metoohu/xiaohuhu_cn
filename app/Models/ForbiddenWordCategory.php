<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 违禁词分类（红线 block / 调性 tone）。
 */
class ForbiddenWordCategory extends Model
{
    public const LEVEL_BLOCK = 'block';

    public const LEVEL_TONE = 'tone';

    protected $fillable = ['slug', 'name', 'level', 'sort'];

    public function words(): HasMany
    {
        return $this->hasMany(ForbiddenWord::class, 'category_id');
    }
}
