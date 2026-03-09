<?php

namespace Database\Seeders;

use App\Models\Admin\AdminRole;
use App\Models\Admin\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = AdminRole::firstOrCreate(
            ['name' => 'super_admin'],
            ['description' => '超级管理员']
        );

        AdminRole::firstOrCreate(
            ['name' => 'admin'],
            ['description' => '管理员']
        );

        AdminRole::firstOrCreate(
            ['name' => 'operator'],
            ['description' => '运营']
        );

        $user = AdminUser::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => '超级管理员',
                'password' => Hash::make('admin123'),
                'status' => 1,
            ]
        );

        if (! $user->roles()->where('name', 'super_admin')->exists()) {
            $user->roles()->attach($superAdmin->id);
        }
    }
}
