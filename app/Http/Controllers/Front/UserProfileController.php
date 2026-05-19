<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\UserArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = auth()->user();

        $pageTab = $request->input('page', 'articles');
        if (! in_array($pageTab, ['articles', 'more'], true)) {
            $pageTab = 'articles';
        }

        $moreTab = $request->input('more', 'showcase');
        if (! in_array($moreTab, ['showcase', 'profile', 'account'], true)) {
            $moreTab = 'showcase';
        }

        $articleTab = $request->input('article_tab', 'all');
        $articlesQuery = $user->userArticles()->with(['category', 'tags'])->latest('updated_at');
        if ($articleTab !== 'all' && in_array($articleTab, UserArticle::statusKeys(), true)) {
            $articlesQuery->where('status', $articleTab);
        }
        $articles = $articlesQuery->paginate(config('front.article_per_page', 15))->withQueryString();

        return view('front.user.profile', compact('user', 'articles', 'articleTab', 'pageTab', 'moreTab'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($request->input('gender') === '') {
            $request->merge(['gender' => null]);
        }

        $request->validate([
            'signature' => ['nullable', 'string', 'max:500'],
            'mood_emoji' => ['nullable', 'string', 'max:32'],
            'mood_text' => ['nullable', 'string', 'max:120'],
            'birthday' => ['nullable', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'gender' => ['nullable', 'string', 'in:male,female,other,secret'],
            'interests' => ['nullable', 'string', 'max:500'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], [
            'avatar.image' => '请上传图片文件',
            'avatar.max' => '头像不能超过 2MB',
            'birthday.before_or_equal' => '生日不能晚于今天',
        ]);

        $data = $request->only(['signature', 'mood_emoji', 'mood_text', 'birthday', 'gender', 'interests', 'occupation']);
        foreach (['birthday', 'gender', 'interests', 'occupation'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === '') {
                $data[$key] = null;
            }
        }

        if ($request->hasFile('avatar')) {
            $dir = trim(config('admin.upload_path', 'uploads'), '/').'/avatars';
            Storage::disk('public')->makeDirectory($dir);
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store($dir, 'public');
        }

        $user->update($data);

        $moreTab = $request->input('redirect_more', 'showcase');
        if (! in_array($moreTab, ['showcase', 'profile'], true)) {
            $moreTab = 'showcase';
        }

        return redirect()->route('front.my.profile', [
            'page' => 'more',
            'more' => $moreTab,
        ])->with('success', '资料已保存');
    }
}
