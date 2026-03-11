<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        $seo = [
            'title' => '留言板 - ' . (Setting::adminName() ?: '小糊涂人生馆'),
            'keywords' => Setting::seoKeywords(),
            'description' => Setting::seoDescription(),
        ];

        return view('front.message.index', compact('seo'));
    }
}
