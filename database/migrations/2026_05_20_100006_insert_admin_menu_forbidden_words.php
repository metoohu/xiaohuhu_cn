<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 后台侧栏增加「违禁词库」「违规记录」菜单项。
 */
return new class extends Migration
{
    public function up(): void
    {
        $items = [
            [
                'route_name' => 'admin.forbidden-words.index',
                'title' => '违禁词库',
                'active_pattern' => 'admin.forbidden-words.*',
                'sort' => 63,
            ],
            [
                'route_name' => 'admin.forbidden-word-violations.index',
                'title' => '违规记录',
                'active_pattern' => 'admin.forbidden-word-violations.*',
                'sort' => 64,
            ],
        ];

        $now = now();
        foreach ($items as $item) {
            if (DB::table('admin_menu_items')->where('route_name', $item['route_name'])->exists()) {
                continue;
            }

            DB::table('admin_menu_items')->insert([
                'parent_id' => null,
                'title' => $item['title'],
                'route_name' => $item['route_name'],
                'url' => null,
                'active_pattern' => $item['active_pattern'],
                'sort' => $item['sort'],
                'is_active' => true,
                'is_divider' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('admin_menu_items')->whereIn('route_name', [
            'admin.forbidden-words.index',
            'admin.forbidden-word-violations.index',
        ])->delete();
    }
};
