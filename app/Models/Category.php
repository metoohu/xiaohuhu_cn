<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    public const STATUS_DISABLED = 0;

    public const STATUS_ENABLED = 1;

    protected $fillable = ['name', 'slug', 'parent_id', 'sort', 'description', 'icon', 'status'];

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort');
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    /**
     * 获取树状结构的分类选项（用于下拉框，带层级缩进）
     * 返回 [id, name] 结构的集合，name 含缩进前缀
     * @param  int|null  $excludeId  排除的分类 ID（如编辑时排除自身以避免循环引用）
     */
    public static function getTreeOptions(?int $excludeId = null): Collection
    {
        $all = static::orderBy('sort')->orderBy('id')->get()->keyBy('id');
        $roots = $all->whereNull('parent_id')->sortBy('sort')->values();
        $result = collect();
        $indent = '　'; // 全角空格

        $add = function ($items, $level = 0) use (&$add, &$result, $all, $excludeId, $indent) {
            foreach ($items->values() as $item) {
                if ($excludeId !== null && (int) $item->id === $excludeId) {
                    continue;
                }
                $prefix = $level > 0 ? str_repeat($indent, $level * 2) . '└ ' : '';
                $result->push((object) ['id' => $item->id, 'name' => $prefix . $item->name]);

                $children = $all->where('parent_id', $item->id)->sortBy('sort')->values();
                if ($children->isNotEmpty()) {
                    $add($children, $level + 1);
                }
            }
        };

        $add($roots);

        return $result;
    }
}
