<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forbidden_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('forbidden_word_categories')->cascadeOnDelete();
            $table->text('word');
            $table->string('match_type', 16)->default('exact');
            $table->text('replacement')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->string('remark')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forbidden_words');
    }
};
