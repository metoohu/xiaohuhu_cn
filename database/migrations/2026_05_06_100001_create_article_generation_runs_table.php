<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 记录每个叶子类目「按自然日」仅一篇的 AI 生成任务，防重复投递。
     */
    public function up(): void
    {
        Schema::create('article_generation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->date('run_date')->comment('应用时区的自然日');
            $table->foreignId('article_id')->nullable()->constrained('articles')->nullOnDelete();
            $table->string('status', 32)->default('pending')->comment('pending/processing/success/failed');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'run_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_generation_runs');
    }
};
