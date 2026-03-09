<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('cover_image')->comment('SEO 标题');
            $table->string('seo_keywords')->nullable()->after('seo_title')->comment('SEO 关键词');
            $table->string('seo_description')->nullable()->after('seo_keywords')->comment('SEO 描述');
            $table->unsignedInteger('click_num')->default(0)->after('seo_description')->comment('阅读量');
            $table->boolean('is_recommend')->default(false)->after('click_num')->comment('是否推荐');
            $table->integer('sort')->default(0)->after('is_recommend')->comment('排序');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_keywords', 'seo_description', 'click_num', 'is_recommend', 'sort']);
        });
    }
};
