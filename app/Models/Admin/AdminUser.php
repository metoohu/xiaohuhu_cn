<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 0;

    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLE;
    }

    public function roles()
    {
        return $this->belongsToMany(AdminRole::class, 'admin_role_user', 'admin_user_id', 'role_id');
    }

    public function loginLogs()
    {
        return $this->hasMany(AdminLoginLog::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}
