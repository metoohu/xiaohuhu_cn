<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const CAPTCHA_SESSION_KEY = 'front_captcha';
    public function showRegisterForm(Request $request): View|RedirectResponse
    {
        if (\App\Models\Setting::get('register_enabled', '1') !== '1') {
            return redirect()->route('front.home')->with('error', '注册功能已关闭');
        }

        return view('front.auth.register', [
            'returnUrl' => $request->query('return_url'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        if (\App\Models\Setting::get('register_enabled', '1') !== '1') {
            return redirect()->route('front.home')->with('error', '注册功能已关闭');
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'captcha' => ['required', function ($attr, $value, $fail) {
                if (strtolower($value) !== strtolower(session(self::CAPTCHA_SESSION_KEY))) {
                    $fail('验证码错误');
                }
            }],
        ], [
            'captcha.required' => '请输入验证码',
        ]);

        session()->forget(self::CAPTCHA_SESSION_KEY);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user = User::where('email', $request->email)->first();
        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        $returnUrl = $request->input('return_url');
        if ($returnUrl && str_starts_with($returnUrl, '/') && ! str_starts_with($returnUrl, '//')) {
            return redirect($returnUrl)->with('success', '注册成功，欢迎加入！');
        }

        return redirect()->route('front.home')->with('success', '注册成功，欢迎加入！');
    }

    public function showLoginForm(Request $request): View
    {
        return view('front.auth.login', [
            'returnUrl' => $request->query('return_url'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'captcha' => ['required', function ($attr, $value, $fail) {
                if (strtolower($value) !== strtolower(session(self::CAPTCHA_SESSION_KEY))) {
                    $fail('验证码错误');
                }
            }],
        ], [
            'captcha.required' => '请输入验证码',
        ]);

        session()->forget(self::CAPTCHA_SESSION_KEY);

        if (! Auth::guard('web')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => '邮箱或密码错误'])->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        $returnUrl = $request->input('return_url');
        if ($returnUrl && str_starts_with($returnUrl, '/') && ! str_starts_with($returnUrl, '//')) {
            return redirect($returnUrl)->with('success', '登录成功');
        }

        return redirect()->route('front.home')->with('success', '登录成功');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('front.home');
    }

    public function captcha()
    {
        $code = Str::lower(Str::random(4));
        session([self::CAPTCHA_SESSION_KEY => $code]);

        $width = 120;
        $height = 40;

        if (! function_exists('imagecreatetruecolor')) {
            return response('GD not available', 500);
        }

        $img = imagecreatetruecolor($width, $height);
        if (! $img) {
            return response('Image create failed', 500);
        }

        $bg = imagecolorallocate($img, 245, 247, 245);
        $textColor = imagecolorallocate($img, 74, 109, 99);
        $lineColor = imagecolorallocate($img, 200, 210, 200);

        imagefill($img, 0, 0, $bg);

        for ($i = 0; $i < 4; $i++) {
            imageline($img, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }

        $charWidth = 8;
        $charHeight = 13;
        $gap = 14;
        $totalTextWidth = strlen($code) * $charWidth + (strlen($code) - 1) * $gap;
        $startX = (int) (($width - $totalTextWidth) / 2);
        $startY = (int) (($height - $charHeight) / 2);

        $x = $startX;
        foreach (str_split($code) as $char) {
            imagestring($img, 5, $x, $startY, $char, $textColor);
            $x += $charWidth + $gap;
        }

        ob_start();
        imagejpeg($img, null, 90);
        $content = ob_get_clean();
        imagedestroy($img);

        return response($content, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
