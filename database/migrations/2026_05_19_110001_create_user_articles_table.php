<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 用户社区稿主表（B2：不入 articles，前台双源之一）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title', 120);
            $table->text('content_public')->nullable()->comment('已对外展示正文（发布后非空）');
            $table->text('content_pending')->nullable()->comment('待审/编辑中正文');
            $table->string('status', 32)->default('draft')->comment('draft|pending_review|published|rejected');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_original')->default(true);
            $table->string('excerpt', 512)->nullable();
            $table->unsignedInteger('click_num')->default(0);
            $table->timestamp('submitted_at')->nullable()->comment('最近一次提交审核时间，用于日额度统计');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'submitted_at']);
            $table->index(['category_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_articles');
    }
};
