<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            [
                'slug' => 'compliance_redline',
                'name' => '合规红线类',
                'level' => 'block',
                'sort' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'slug' => 'tone_violation',
                'name' => '调性违规类',
                'level' => 'tone',
                'sort' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            if (! DB::table('forbidden_word_categories')->where('slug', $row['slug'])->exists()) {
                DB::table('forbidden_word_categories')->insert($row);
            }
        }
    }

    public function down(): void
    {
        DB::table('forbidden_word_categories')->whereIn('slug', ['compliance_redline', 'tone_violation'])->delete();
    }
};
