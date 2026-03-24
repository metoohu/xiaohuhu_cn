<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Support\CommentContentFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $keyword = $request->input('keyword');

        $comments = Comment::query()
            ->with(['article', 'user', 'parent'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($keyword, fn ($q) => $q->where('content', 'like', "%{$keyword}%"))
            ->latest()
            ->paginate(config('admin.per_page', 10));

        return view('admin.comments.index', compact('comments'));
    }

    public function show(Comment $comment): View
    {
        $comment->load(['article', 'user', 'parent', 'replies']);

        return view('admin.comments.show', compact('comment'));
    }

    public function edit(Comment $comment): View
    {
        return view('admin.comments.edit', compact('comment'));
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'content' => 'required|string|max:2000',
        ]);

        if ($comment->user_id) {
            $stickerErr = CommentContentFormatter::validateUserStickers($request->input('content', ''), (int) $comment->user_id);
            if ($stickerErr !== null) {
                return back()->withInput()->withErrors(['content' => $stickerErr]);
            }
        }

        $comment->update($request->only('status', 'content'));

        return redirect()->route('admin.comments.index')->with('success', '评论已更新');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->replies()->delete();
        $comment->delete();

        return redirect()->route('admin.comments.index')->with('success', '评论已删除');
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['status' => 'approved']);

        return back()->with('success', '评论已通过');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        $comment->update(['status' => 'rejected']);

        return back()->with('success', '评论已拒绝');
    }
}
