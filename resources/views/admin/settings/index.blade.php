@extends('admin.layouts.master')

@section('title', '系统设置 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-2xl">
    <h2 class="text-xl font-bold mb-4">系统设置</h2>
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">系统名称</label>
                <input type="text" name="admin_name" value="{{ old('admin_name', $settings['admin_name'] ?? '') }}" placeholder="显示于后台侧栏、登录页等" class="w-full rounded border-slate-300">
                <p class="text-xs text-slate-500 mt-1">用于后台标题、登录页、前台页脚等处</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">网站标题</label>
                <input type="text" name="site_title" value="{{ old('site_title', $settings['site_title'] ?? '') }}" placeholder="前台首页、预设页面标题" class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">SEO 关键词</label>
                <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $settings['seo_keywords'] ?? '') }}" placeholder="多个关键词以逗号分隔" class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">SEO 描述</label>
                <textarea name="seo_description" rows="3" placeholder="网站简介，用于搜索引擎摘要" class="w-full rounded border-slate-300">{{ old('seo_description', $settings['seo_description'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">网站 LOGO</label>
                @if (!empty($settings['site_logo']))
                    <p class="text-sm text-slate-500 mb-1">当前已设置</p>
                @endif
                <input type="file" name="site_logo" accept="image/*" class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">备案信息</label>
                <input type="text" name="site_icp" value="{{ old('site_icp', $settings['site_icp'] ?? '') }}" class="w-full rounded border-slate-300">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">联系方式</label>
                <textarea name="site_contact" rows="3" class="w-full rounded border-slate-300">{{ old('site_contact', $settings['site_contact'] ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">评论开关</label>
                <label><input type="radio" name="comment_enabled" value="1" {{ ($settings['comment_enabled'] ?? '1') == '1' ? 'checked' : '' }}> 开启</label>
                <label class="ml-4"><input type="radio" name="comment_enabled" value="0" {{ ($settings['comment_enabled'] ?? '1') == '0' ? 'checked' : '' }}> 关闭</label>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">注册开关</label>
                <label><input type="radio" name="register_enabled" value="1" {{ ($settings['register_enabled'] ?? '1') == '1' ? 'checked' : '' }}> 开启</label>
                <label class="ml-4"><input type="radio" name="register_enabled" value="0" {{ ($settings['register_enabled'] ?? '1') == '0' ? 'checked' : '' }}> 关闭</label>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">保存</button>
        </div>
    </form>
</div>
@endsection
