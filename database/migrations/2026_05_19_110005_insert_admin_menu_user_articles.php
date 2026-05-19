<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 后台侧栏增加「用户投稿」入口（数据表驱动菜单）。
 */
return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('admin_menu_items')->where('route_name', 'admin.user-articles.index')->exists();
        if ($exists) {
            return;
        }

        $now = now();
        DB::table('admin_menu_items')->insert([
            'parent_id' => null,
            'title' => '用户投稿',
            'route_name' => 'admin.user-articles.index',
            'url' => null,
            'active_pattern' => 'admin.user-articles.*',
            'sort' => 62,
            'is_active' => true,
            'is_divider' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('admin_menu_items')->where('route_name', 'admin.user-articles.index')->delete();
    }
};
