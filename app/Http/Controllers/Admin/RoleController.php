<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = AdminRole::withCount('users')->latest()->paginate(config('admin.per_page', 10));

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:admin_roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        AdminRole::create($request->only('name', 'description'));

        return redirect()->route('admin.roles.index')->with('success', '角色创建成功');
    }

    public function show(AdminRole $role): View
    {
        $role->load('users');

        return view('admin.roles.show', compact('role'));
    }

    public function edit(AdminRole $role): View
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, AdminRole $role): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:admin_roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
        ]);

        $role->update($request->only('name', 'description'));

        return redirect()->route('admin.roles.index')->with('success', '角色更新成功');
    }

    public function destroy(AdminRole $role): RedirectResponse
    {
        if ($role->name === 'super_admin') {
            return back()->withErrors(['error' => '不能删除超级管理员角色']);
        }

        $role->users()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', '角色已删除');
    }
}
