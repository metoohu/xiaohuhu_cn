<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminOperationLog;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $banned = $request->input('banned');

        $members = User::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($q2) use ($keyword) {
                    $q2->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($banned === '1', fn ($q) => $q->whereNotNull('comment_banned_at'))
            ->when($banned === '0', fn ($q) => $q->whereNull('comment_banned_at'))
            ->withCount(['stickers', 'comments'])
            ->latest()
            ->paginate(config('admin.per_page', 10))
            ->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    public function show(User $user): View
    {
        $user->loadCount(['stickers', 'comments']);
        $recentComments = Comment::query()
            ->where('user_id', $user->id)
            ->with('article:id,title')
            ->latest()
            ->limit(15)
            ->get();

        return view('admin.members.show', ['member' => $user, 'recentComments' => $recentComments]);
    }

    public function mute(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'comment_ban_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'comment_banned_at' => now(),
            'comment_ban_reason' => $request->input('comment_ban_reason'),
        ]);

        AdminOperationLog::log('禁言注册会员: '.$user->email, '前台会员');

        return back()->with('success', '已禁言，该用户不可再发表评论');
    }

    public function unmute(User $user): RedirectResponse
    {
        $user->update([
            'comment_banned_at' => null,
            'comment_ban_reason' => null,
        ]);

        AdminOperationLog::log('解除禁言: '.$user->email, '前台会员');

        return back()->with('success', '已解除禁言');
    }
}
