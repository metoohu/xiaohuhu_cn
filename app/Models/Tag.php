<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 用户投稿标签（规范化 name + slug 去重）。
 */
class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function userArticles(): BelongsToMany
    {
        return $this->belongsToMany(UserArticle::class, 'user_article_tag');
    }
}
