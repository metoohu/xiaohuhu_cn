<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 用户稿与标签多对多（最多 3 个标签在应用层校验）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_article_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_article_id')->constrained('user_articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->unique(['user_article_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_article_tag');
    }
};
