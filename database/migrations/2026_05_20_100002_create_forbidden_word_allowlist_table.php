<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forbidden_word_allowlist', function (Blueprint $table) {
            $table->id();
            $table->text('phrase');
            $table->boolean('is_enabled')->default(true);
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forbidden_word_allowlist');
    }
};
