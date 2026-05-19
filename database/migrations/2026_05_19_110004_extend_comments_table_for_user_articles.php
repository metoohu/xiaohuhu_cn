<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 评论支持挂运营文章或用户社区稿（二选一，应用层校验互斥）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('user_article_id')->nullable()->after('article_id')->constrained('user_articles')->cascadeOnDelete();
        });

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'pgsql'], true)) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropForeign(['article_id']);
            });
            Schema::table('comments', function (Blueprint $table) {
                $table->unsignedBigInteger('article_id')->nullable()->change();
            });
            Schema::table('comments', function (Blueprint $table) {
                $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            });

            return;
        }

        // SQLite 等：依赖 Laravel 的 change() 重建表逻辑
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('article_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'pgsql'], true)) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropForeign(['user_article_id']);
            });
            Schema::table('comments', function (Blueprint $table) {
                $table->dropColumn('user_article_id');
            });
            Schema::table('comments', function (Blueprint $table) {
                $table->dropForeign(['article_id']);
            });
            Schema::table('comments', function (Blueprint $table) {
                $table->unsignedBigInteger('article_id')->nullable(false)->change();
            });
            Schema::table('comments', function (Blueprint $table) {
                $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            });

            return;
        }

        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['user_article_id']);
            $table->dropColumn('user_article_id');
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('article_id')->nullable(false)->change();
        });
    }
};
