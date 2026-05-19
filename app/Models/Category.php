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

    /** AI 情感文文风（与后台下拉一致） */
    public const AI_TONE_HEALING = 'healing';

    public const AI_TONE_JOURNEY = 'journey';

    public const AI_TONE_TRIVIAL = 'trivial';

    public const AI_TONE_SOBER = 'sober';

    public const AI_TONE_QUIET = 'quiet';

    protected $fillable = ['name', 'slug', 'parent_id', 'sort', 'description', 'icon', 'status', 'ai_tone', 'user_can_submit'];

    protected function casts(): array
    {
        return [
            'user_can_submit' => 'boolean',
        ];
    }

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

    public function userArticles()
    {
        return $this->hasMany(UserArticle::class);
    }

    /**
     * 可选文风键名列表（用于校验与下拉）。
     *
     * @return list<string>
     */
    public static function aiToneKeys(): array
    {
        return [
            self::AI_TONE_HEALING,
            self::AI_TONE_JOURNEY,
            self::AI_TONE_TRIVIAL,
            self::AI_TONE_SOBER,
            self::AI_TONE_QUIET,
        ];
    }

    /**
     * 后台展示用：键 => 中文名
     *
     * @return array<string, string>
     */
    public static function aiToneLabels(): array
    {
        return [
            self::AI_TONE_HEALING => '治愈',
            self::AI_TONE_JOURNEY => '人生旅途',
            self::AI_TONE_TRIVIAL => '生活琐碎',
            self::AI_TONE_SOBER => '人间清醒',
            self::AI_TONE_QUIET => '宁静角落',
        ];
    }

    /**
     * 解析实际文风：未配置时默认「治愈」。
     */
    public function resolvedAiTone(): string
    {
        $tone = $this->ai_tone;

        return in_array($tone, self::aiToneKeys(), true) ? $tone : self::AI_TONE_HEALING;
    }

    /**
     * 是否为叶子类目（无子分类记录即视为叶子）。
     */
    public function isLeaf(): bool
    {
        return ! static::query()->where('parent_id', $this->id)->exists();
    }

    /**
     * 启用且无子分类：参与「每叶子类目每日一篇」调度。
     */
    public function scopeEnabledLeaves(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENABLED)
            ->whereDoesntHave('children');
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

    /**
     * 用户投稿可选分类：启用且后台勾选「允许用户投稿」。
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    public static function getSubmitSelectOptions()
    {
        return static::query()
            ->enabled()
            ->where('user_can_submit', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id']);
    }
}
