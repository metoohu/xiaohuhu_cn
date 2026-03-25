<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'disabled_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'comment_ban_reason')) {
                $table->timestamp('disabled_at')->nullable()->after('comment_ban_reason')->comment('禁用时间，非空则不可登录前台');
            } else {
                $table->timestamp('disabled_at')->nullable()->comment('禁用时间，非空则不可登录前台');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'disabled_at')) {
                $table->dropColumn('disabled_at');
            }
        });
    }
};
