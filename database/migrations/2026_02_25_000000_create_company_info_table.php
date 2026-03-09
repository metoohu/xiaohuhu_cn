<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 运行迁移.
     */
    public function up(): void
    {
        Schema::create('company_info', function (Blueprint $table) {
            // 主键 ID
            $table->bigIncrements('id');

            // 公司代码，如 000001
            $table->string('code', 20)->nullable(false);

            // 公司简称
            $table->string('abbreviation', 100)->nullable(false);

            // 联系电话
            $table->string('contact_number', 50)->nullable();

            // 经营范围
            $table->text('nature_business')->nullable();

            // 采集时间
            $table->dateTime('capture_time')->nullable(false);

            // 索引，便于根据公司代码查询
            $table->index('code');
        });
    }

    /**
     * 回滚迁移.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_info');
    }
};

