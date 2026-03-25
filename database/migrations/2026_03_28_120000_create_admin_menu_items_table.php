<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('admin_menu_items')->nullOnDelete();
            $table->string('title', 100)->default('');
            $table->string('route_name', 191)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('active_pattern', 191)->nullable()->comment('路由高亮，如 admin.users.*，空则根据 route_name 推断');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_divider')->default(false);
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['parent_id' => null, 'title' => '仪表盘', 'route_name' => 'admin.dashboard', 'url' => null, 'active_pattern' => null, 'sort' => 10, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '用户管理', 'route_name' => 'admin.users.index', 'url' => null, 'active_pattern' => null, 'sort' => 20, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '前台会员', 'route_name' => 'admin.members.index', 'url' => null, 'active_pattern' => null, 'sort' => 30, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '角色管理', 'route_name' => 'admin.roles.index', 'url' => null, 'active_pattern' => null, 'sort' => 40, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '分类管理', 'route_name' => 'admin.categories.index', 'url' => null, 'active_pattern' => null, 'sort' => 50, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '文章管理', 'route_name' => 'admin.articles.index', 'url' => null, 'active_pattern' => null, 'sort' => 60, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '评论管理', 'route_name' => 'admin.comments.index', 'url' => null, 'active_pattern' => null, 'sort' => 70, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '', 'route_name' => null, 'url' => null, 'active_pattern' => null, 'sort' => 80, 'is_active' => true, 'is_divider' => true],
            ['parent_id' => null, 'title' => '系统设置', 'route_name' => 'admin.settings.index', 'url' => null, 'active_pattern' => null, 'sort' => 90, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '操作日志', 'route_name' => 'admin.logs.operation', 'url' => null, 'active_pattern' => 'admin.logs.*', 'sort' => 100, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '备份管理', 'route_name' => 'admin.backups.index', 'url' => null, 'active_pattern' => null, 'sort' => 110, 'is_active' => true, 'is_divider' => false],
            ['parent_id' => null, 'title' => '菜单管理', 'route_name' => 'admin.menu-items.index', 'url' => null, 'active_pattern' => 'admin.menu-items.*', 'sort' => 120, 'is_active' => true, 'is_divider' => false],
        ];

        foreach ($rows as $r) {
            DB::table('admin_menu_items')->insert(array_merge($r, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_menu_items');
    }
};
