<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name')->comment('URL 标识');
        });

        // 為現有分類補上 slug（確保唯一）
        $used = [];
        foreach (DB::table('categories')->orderBy('id')->get() as $cat) {
            $base = Str::slug($cat->name ?: 'cat');
            $slug = $base;
            $i = 0;
            while (in_array($slug, $used, true)) {
                $slug = $base . '-' . (++$i);
            }
            $used[] = $slug;
            DB::table('categories')->where('id', $cat->id)->update(['slug' => $slug]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
