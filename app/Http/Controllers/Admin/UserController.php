<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Admin\AdminUser;
use App\Models\Admin\AdminOperationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $status = $request->input('status');

        $users = AdminUser::query()
            ->when($keyword, fn ($q) => $q->where('name', 'like', "%{$keyword}%")->orWhere('email', 'like', "%{$keyword}%"))
            ->when($status !== null, fn ($q) => $q->where('status', $status))
            ->with('roles')
            ->latest()
            ->paginate(config('admin.per_page', 10));

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = \App\Models\Admin\AdminRole::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = AdminUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        if ($request->filled('roles')) {
            $user->roles()->sync($request->roles);
        }

        AdminOperationLog::log('创建后台用户: ' . $user->name, '用户管理');

        return redirect()->route('admin.users.index')->with('success', '用户创建成功');
    }

    public function show(AdminUser $user): View
    {
        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function edit(AdminUser $user): View
    {
        $roles = \App\Models\Admin\AdminRole::all();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UserRequest $request, AdminUser $user): RedirectResponse
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $user->roles()->sync($request->roles ?? []);

        AdminOperationLog::log('更新后台用户: ' . $user->name, '用户管理');

        return redirect()->route('admin.users.index')->with('success', '用户更新成功');
    }

    public function destroy(AdminUser $user): RedirectResponse
    {
        if ($user->isSuperAdmin()) {
            return back()->withErrors(['error' => '不能删除超级管理员']);
        }

        $user->delete();

        AdminOperationLog::log('删除后台用户: ' . $user->name, '用户管理');

        return redirect()->route('admin.users.index')->with('success', '用户已删除');
    }

    public function batchAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:enable,disable,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:admin_users,id',
        ]);

        $users = AdminUser::whereIn('id', $request->ids)->get();

        foreach ($users as $user) {
            if ($user->isSuperAdmin()) {
                continue;
            }
            match ($request->action) {
                'enable' => $user->update(['status' => 1]),
                'disable' => $user->update(['status' => 0]),
                'delete' => $user->delete(),
            };
        }

        return back()->with('success', '批量操作完成');
    }

    public function export(Request $request)
    {
        $users = AdminUser::query()
            ->when($request->keyword, fn ($q) => $q->where('name', 'like', "%{$request->keyword}%")->orWhere('email', 'like', "%{$request->keyword}%"))
            ->get(['id', 'name', 'email', 'status', 'created_at']);

        $filename = 'admin_users_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($users) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['ID', '用户名', '邮箱', '状态', '创建时间']);

            foreach ($users as $u) {
                fputcsv($handle, [$u->id, $u->name, $u->email, $u->status ? '启用' : '禁用', $u->created_at->format('Y-m-d H:i')]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
