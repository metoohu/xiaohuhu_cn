<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminLoginLog;
use App\Models\Admin\AdminOperationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::guard('admin')->user();

        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('admin')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        AdminOperationLog::log('更新个人资料', '个人中心');

        return back()->with('success', '资料已更新');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = Auth::guard('admin')->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => '当前密码错误']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        AdminOperationLog::log('修改密码', '个人中心');

        return back()->with('success', '密码已更新');
    }

    public function loginLogs(): View
    {
        $user = Auth::guard('admin')->user();
        $logs = AdminLoginLog::where('admin_user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('admin.profile.logs', compact('logs'));
    }
}
