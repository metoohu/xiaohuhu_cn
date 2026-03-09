<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name')->nullable()->comment('访客昵称');
            $table->string('author_email')->nullable()->comment('访客邮箱');
            $table->text('content')->comment('评论内容');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->string('status')->default('pending')->comment('pending=待审核，approved=已通过，rejected=已拒绝');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
