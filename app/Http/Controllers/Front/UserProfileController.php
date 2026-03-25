<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function edit(): View
    {
        return view('front.user.profile', ['user' => auth()->user()]);
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

        return back()->with('success', '资料已保存');
    }
}
