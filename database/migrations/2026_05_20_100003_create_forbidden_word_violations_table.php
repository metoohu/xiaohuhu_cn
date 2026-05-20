<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forbidden_word_violations', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 32);
            $table->unsignedBigInteger('content_id')->nullable();
            $table->string('content_title_snapshot')->nullable();
            $table->string('field', 64);
            $table->string('matched_word', 255);
            $table->string('category_slug', 64);
            $table->enum('action', ['block', 'replace', 'import_reject']);
            $table->text('original_excerpt')->nullable();
            $table->text('replaced_excerpt')->nullable();
            $table->foreignId('handler_admin_id')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->enum('status', ['pending', 'rejected', 'replaced', 'resolved'])->default('pending');
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index('checked_at');
            $table->index(['category_slug', 'status']);
            $table->index(['content_type', 'content_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forbidden_word_violations');
    }
};
