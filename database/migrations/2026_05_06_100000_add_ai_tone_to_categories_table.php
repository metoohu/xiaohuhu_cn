<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 叶子类目可选 AI 情感文文风；空则默认「治愈」。
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('ai_tone', 32)
                ->nullable()
                ->after('description')
                ->comment('AI情感文文风：healing/journey/trivial/sober/quiet，空=治愈');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('ai_tone');
        });
    }
};
