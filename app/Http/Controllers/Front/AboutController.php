<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        $categories = Category::enabled()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->withCount(['articles' => fn ($q) => $q->where('status', 'published')])
            ->get();

        $content = Setting::get('about_content', '');
        $contact = Setting::get('site_contact', '');
        $icp = Setting::get('site_icp', '');

        $seo = [
            'title' => '关于我们 - ' . (Setting::adminName() ?: '内容展示'),
            'keywords' => Setting::seoKeywords(),
            'description' => mb_substr(strip_tags($content), 0, 150) ?: Setting::seoDescription(),
        ];

        return view('front.about.index', compact('categories', 'content', 'contact', 'icp', 'seo'));
    }
}
