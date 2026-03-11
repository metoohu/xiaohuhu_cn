<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => '欢迎来到心灵归宿新闻资讯',
                'summary' => '这里是新闻资讯栏目，将为您带来最新的动态与资讯。',
                'content' => '<p>欢迎访问心灵归宿的新闻资讯栏目！</p><p>这里将定期更新各类资讯动态，与您分享有价值的内容。感谢您的关注与支持。</p>',
                'source' => '心灵归宿',
                'status' => 'published',
                'published_at' => now(),
            ],
            [
                'title' => '技术分享：Laravel 开发实践',
                'summary' => '分享 Laravel 框架在项目开发中的实践经验与技巧。',
                'content' => '<p>Laravel 是当今最流行的 PHP 框架之一，以其优雅的语法和强大的功能深受开发者喜爱。</p><p>本文将分享在实际项目开发中的一些经验与技巧，希望能对大家有所帮助。</p>',
                'source' => '心灵归宿',
                'status' => 'published',
                'published_at' => now()->subDay(),
            ],
            [
                'title' => '学习与成长：记录编程路上的点滴',
                'summary' => '记录学习历程，分享技术心得，与你一起探索技术的奥秘。',
                'content' => '<p>编程之路漫漫，每一次学习都是成长。</p><p>在这里，我们记录学习历程，分享技术心得，与你一起探索技术的奥秘。愿我们都能在技术的世界里不断进步。</p>',
                'source' => '心灵归宿',
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ],
        ];

        foreach ($items as $item) {
            News::firstOrCreate(
                ['title' => $item['title']],
                array_merge($item, ['click_num' => 0, 'sort' => 0])
            );
        }
    }
}
