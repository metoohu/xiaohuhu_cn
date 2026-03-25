<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birthday')->nullable()->after('mood_text')->comment('生日');
            $table->string('gender', 20)->nullable()->after('birthday')->comment('性别');
            $table->string('interests', 500)->nullable()->after('gender')->comment('兴趣爱好');
            $table->string('occupation', 100)->nullable()->after('interests')->comment('职业');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['birthday', 'gender', 'interests', 'occupation']);
        });
    }
};
