<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AdminMenuItem extends Model
{
    protected $table = 'admin_menu_items';

    protected $fillable = [
        'parent_id',
        'title',
        'route_name',
        'url',
        'active_pattern',
        'sort',
        'is_active',
        'is_divider',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_divider' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AdminMenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AdminMenuItem::class, 'parent_id')->orderBy('sort')->orderBy('id');
    }

    /**
     * 侧栏用：仅启用项，树形（无限层由集合递归构建）
     */
    public static function sidebarTree(): \Illuminate\Support\Collection
    {
        $all = static::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        if ($all->isEmpty()) {
            return collect();
        }

        return static::buildTreeCollection($all, null);
    }

    /**
     * 后台管理页：含禁用项
     */
    public static function managementTree(): \Illuminate\Support\Collection
    {
        $all = static::query()->orderBy('sort')->orderBy('id')->get();

        return static::buildTreeCollection($all, null);
    }

    protected static function buildTreeCollection(\Illuminate\Support\Collection $all, ?int $parentId): \Illuminate\Support\Collection
    {
        return $all
            ->filter(function (AdminMenuItem $item) use ($parentId) {
                if ($parentId === null) {
                    return $item->parent_id === null;
                }

                return (int) $item->parent_id === $parentId;
            })
            ->values()
            ->map(function (AdminMenuItem $item) use ($all) {
                $children = static::buildTreeCollection($all, (int) $item->id);
                $item->setRelation('children', $children);

                return $item;
            });
    }

    public function resolveHref(): ?string
    {
        if ($this->is_divider || ! $this->is_active) {
            return null;
        }

        if ($this->route_name && Route::has($this->route_name)) {
            return route($this->route_name);
        }

        if ($this->url !== null && $this->url !== '') {
            $u = trim($this->url);
            if (str_starts_with($u, 'http://') || str_starts_with($u, 'https://')) {
                return $u;
            }
            if (str_starts_with($u, '/')) {
                return url($u);
            }

            return url('/'.$u);
        }

        return null;
    }

    public function isRouteActive(): bool
    {
        if (! request()->route()) {
            return false;
        }

        if ($this->active_pattern) {
            return request()->routeIs($this->active_pattern);
        }

        if ($this->route_name && Route::has($this->route_name)) {
            $name = $this->route_name;
            if (str_ends_with($name, '.index')) {
                $prefix = Str::beforeLast($name, '.');

                return request()->routeIs($prefix.'.*');
            }

            return request()->routeIs($name);
        }

        return false;
    }
}
