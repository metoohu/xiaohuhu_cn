<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('角色名称');
            $table->string('description')->nullable()->comment('角色描述');
            $table->timestamps();
        });

        Schema::create('admin_role_user', function (Blueprint $table) {
            $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('admin_roles')->cascadeOnDelete();
            $table->primary(['admin_user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role_user');
        Schema::dropIfExists('admin_roles');
    }
};
