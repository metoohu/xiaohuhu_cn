<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\UserSticker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserStickerController extends Controller
{
    public function index(): View
    {
        $stickers = auth()->user()->stickers()->get();

        return view('front.user.stickers', compact('stickers'));
    }

    public function json(): JsonResponse
    {
        $stickers = auth()->user()->stickers()->get(['id', 'image_path', 'sort']);

        $data = $stickers->map(fn (UserSticker $s) => [
            'id' => $s->id,
            'url' => Storage::url($s->image_path),
        ]);

        return response()->json(['stickers' => $data]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $max = (int) config('front.stickers.max_per_user', 50);
        $maxKb = (int) config('front.stickers.max_kb', 512);
        $user = auth()->user();

        if ($user->stickers()->count() >= $max) {
            $msg = '最多保存 '.$max.' 个表情包，请先删除部分后再上传';

            return $request->wantsJson()
                ? response()->json(['message' => $msg], 422)
                : back()->with('error', $msg);
        }

        $request->validate([
            'image' => ['required', 'image', 'max:'.($maxKb * 1024)],
        ], [
            'image.required' => '请选择图片',
            'image.image' => '请上传图片文件',
            'image.max' => '图片不能超过 '.$maxKb.'KB',
        ]);

        $dir = config('front.stickers.upload_path', 'uploads/stickers');
        Storage::disk('public')->makeDirectory($dir);
        $path = $request->file('image')->store($dir, 'public');

        $sort = (int) $user->stickers()->max('sort') + 1;
        $sticker = $user->stickers()->create([
            'image_path' => $path,
            'sort' => $sort,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => '已添加',
                'sticker' => [
                    'id' => $sticker->id,
                    'url' => Storage::url($sticker->image_path),
                ],
            ]);
        }

        return back()->with('success', '表情包已上传');
    }

    public function destroy(Request $request, UserSticker $userSticker): JsonResponse|RedirectResponse
    {
        abort_unless($userSticker->user_id === auth()->id(), 403);

        Storage::disk('public')->delete($userSticker->image_path);
        $userSticker->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => '已删除']);
        }

        return back()->with('success', '已删除');
    }
}
