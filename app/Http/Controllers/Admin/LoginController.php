<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminLoginLog;
use App\Models\Admin\AdminUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'captcha' => 'nullable|string',
        ]);

        if (config('captcha')) {
            $request->validate(['captcha' => 'required|captcha']);
        }

        $user = AdminUser::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            AdminLoginLog::create([
                'admin_user_id' => null,
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => AdminLoginLog::STATUS_FAILED,
            ]);

            return back()->withErrors(['email' => '账号或密码错误'])->withInput($request->only('email'));
        }

        if (! $user->isEnabled()) {
            return back()->withErrors(['email' => '账号已被禁用'])->withInput($request->only('email'));
        }

        Auth::guard('admin')->login($user, $request->boolean('remember'));

        AdminLoginLog::create([
            'admin_user_id' => $user->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => AdminLoginLog::STATUS_SUCCESS,
        ]);

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function captcha()
    {
        if (class_exists(\Mews\Captcha\Facades\Captcha::class)) {
            return \Mews\Captcha\Facades\Captcha::create('default');
        }

        return response('', 404);
    }

    public function showForgotPasswordForm(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = AdminUser::where('email', $request->email)->first();
        if (! $user) {
            return back()->withErrors(['email' => '该邮箱未注册'])->withInput();
        }

        $token = Str::random(64);
        \DB::table('admin_password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = route('admin.reset-password', ['token' => $token]) . '?email=' . urlencode($request->email);

        return back()->with('status', '重置链接已发送至您的邮箱（演示模式：' . $resetUrl . '）');
    }

    public function showResetPasswordForm(Request $request, ?string $token = null): View
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $record = \DB::table('admin_password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => '无效的重置链接或已过期'])->withInput();
        }

        $user = AdminUser::where('email', $request->email)->first();
        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        \DB::table('admin_password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('admin.login')->with('status', '密码已重置，请登录');
    }
}
