<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->string('action')->comment('操作');
            $table->string('module')->nullable()->comment('模块');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('extra')->nullable()->comment('额外数据');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_operation_logs');
    }
};
