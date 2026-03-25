<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'signature',
        'mood_emoji',
        'mood_text',
        'birthday',
        'gender',
        'interests',
        'occupation',
        'comment_banned_at',
        'comment_ban_reason',
        'disabled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'comment_banned_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    public function stickers(): HasMany
    {
        return $this->hasMany(UserSticker::class)->orderBy('sort')->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function isCommentBanned(): bool
    {
        return $this->comment_banned_at !== null;
    }

    public function isDisabled(): bool
    {
        return $this->disabled_at !== null;
    }
}
