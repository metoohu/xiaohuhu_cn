<?php

namespace App\Models;

use App\Models\Admin\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $fillable = ['title', 'content', 'category_id', 'admin_user_id', 'status', 'review_comment', 'reviewed_by', 'reviewed_at', 'cover_image', 'seo_title', 'seo_keywords', 'seo_description', 'click_num', 'is_recommend', 'sort'];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REVIEW = 'review';

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
