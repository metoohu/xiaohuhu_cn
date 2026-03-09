<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = 'admin_settings_' . $key;

        return Cache::remember($cacheKey, config('admin.cache_time', 5) * 60, function () use ($key, $default) {
            $item = static::where('key', $key)->first();

            return $item ? $item->value : $default;
        });
    }

    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
        Cache::forget('admin_settings_' . $key);
    }

    public static function clearCache(): void
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget('admin_settings_' . $key);
        }
    }

    /**
     * 取得系统名称（后台可编辑，优先从设置读取，空值时回退 config）
     */
    public static function adminName(): string
    {
        $name = static::get('admin_name', config('admin.name'));
        return (string) ($name ?: config('admin.name'));
    }

    /**
     * 取得 SEO 预设关键词
     */
    public static function seoKeywords(): string
    {
        $v = static::get('seo_keywords', config('front.seo.keywords'));
        return (string) ($v ?: config('front.seo.keywords'));
    }

    /**
     * 取得 SEO 预设描述
     */
    public static function seoDescription(): string
    {
        $v = static::get('seo_description', config('front.seo.description'));
        return (string) ($v ?: config('front.seo.description'));
    }
}
