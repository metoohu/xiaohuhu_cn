<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('password')->comment('头像路径');
            $table->string('signature', 500)->nullable()->after('avatar')->comment('个性签名');
            $table->string('mood_emoji', 32)->nullable()->after('signature')->comment('心情表情');
            $table->string('mood_text', 120)->nullable()->after('mood_emoji')->comment('心情文字');
            $table->timestamp('comment_banned_at')->nullable()->after('mood_text')->comment('禁言时间，非空则不可评论');
            $table->string('comment_ban_reason', 500)->nullable()->after('comment_banned_at')->comment('禁言原因');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'signature', 'mood_emoji', 'mood_text',
                'comment_banned_at', 'comment_ban_reason',
            ]);
        });
    }
};
