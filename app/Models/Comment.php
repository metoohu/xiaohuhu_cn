<?php

namespace App\Models;

use App\Support\CommentContentFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = ['article_id', 'user_id', 'author_name', 'author_email', 'content', 'parent_id', 'status'];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /** 前台/后台展示用：表情占位符转图片，文本转义 */
    public function getContentHtmlAttribute(): string
    {
        return CommentContentFormatter::toHtml($this->content ?? '');
    }
}
