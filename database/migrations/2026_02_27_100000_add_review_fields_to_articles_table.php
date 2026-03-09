<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('review_comment', 500)->nullable()->after('status')->comment('审核意见（驳回时填写）');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_comment')->comment('审核人ID');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by')->comment('审核时间');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['review_comment', 'reviewed_by', 'reviewed_at']);
        });
    }
};
