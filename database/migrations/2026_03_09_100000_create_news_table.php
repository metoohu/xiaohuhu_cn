<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('标题');
            $table->string('summary')->nullable()->comment('摘要');
            $table->text('content')->comment('内容');
            $table->string('cover_image')->nullable()->comment('封面图');
            $table->string('source')->nullable()->comment('来源');
            $table->string('status')->default('published')->comment('draft=草稿，published=发布');
            $table->unsignedInteger('click_num')->default(0)->comment('点击量');
            $table->unsignedInteger('sort')->default(0)->comment('排序');
            $table->timestamp('published_at')->nullable()->comment('发布时间');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
