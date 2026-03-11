<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            [
                'name' => '人间清醒',
                'slug' => 'awake',
                'description' => '理性思考、人生感悟类内容',
                'sort' => 10,
                'children' => [
                    ['name' => '生活洞察', 'slug' => 'insight', 'description' => '日常点滴的深度思考', 'sort' => 1],
                    ['name' => '认知提升', 'slug' => 'growth', 'description' => '打破思维局限的内容', 'sort' => 2],
                    ['name' => '书单推荐', 'slug' => 'books', 'description' => '提升认知的书籍分享', 'sort' => 3],
                ],
            ],
            [
                'name' => '治愈文字',
                'slug' => 'healing',
                'description' => '温暖、治愈类文字内容',
                'sort' => 20,
                'children' => [
                    ['name' => '暖心短句', 'slug' => 'short', 'description' => '简短治愈的文案', 'sort' => 1],
                    ['name' => '心情随笔', 'slug' => 'essay', 'description' => '细腻的情感文字', 'sort' => 2],
                    ['name' => '读者故事', 'slug' => 'story', 'description' => '读者的治愈故事分享', 'sort' => 3],
                ],
            ],
            [
                'name' => '宁静角落',
                'slug' => 'peace',
                'description' => '营造宁静氛围的内容',
                'sort' => 30,
                'children' => [
                    ['name' => '冥想时刻', 'slug' => 'meditate', 'description' => '冥想、放松的方法', 'sort' => 1],
                    ['name' => '自然随笔', 'slug' => 'nature', 'description' => '自然与宁静的文字', 'sort' => 2],
                    ['name' => '轻音乐推荐', 'slug' => 'music', 'description' => '搭配文字的治愈音乐', 'sort' => 3],
                ],
            ],
        ];

        foreach ($structure as $parentData) {
            $children = $parentData['children'] ?? [];
            unset($parentData['children']);

            $parent = Category::updateOrCreate(
                ['slug' => $parentData['slug']],
                array_merge($parentData, ['parent_id' => null, 'status' => 1])
            );

            foreach ($children as $childData) {
                Category::updateOrCreate(
                    ['slug' => $childData['slug']],
                    array_merge($childData, [
                        'parent_id' => $parent->id,
                        'status' => 1,
                    ])
                );
            }
        }
    }
}
