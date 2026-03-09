<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminOperationLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'admin_name' => Setting::get('admin_name', config('admin.name')),
            'site_title' => Setting::get('site_title', Setting::adminName()),
            'seo_keywords' => Setting::get('seo_keywords', config('front.seo.keywords')),
            'seo_description' => Setting::get('seo_description', config('front.seo.description')),
            'site_logo' => Setting::get('site_logo'),
            'site_icp' => Setting::get('site_icp'),
            'site_contact' => Setting::get('site_contact'),
            'comment_enabled' => Setting::get('comment_enabled', '1'),
            'register_enabled' => Setting::get('register_enabled', '1'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'admin_name' => 'nullable|string|max:100',
            'site_title' => 'nullable|string|max:100',
            'seo_keywords' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'site_logo' => 'nullable|image|max:2048',
            'site_icp' => 'nullable|string|max:255',
            'site_contact' => 'nullable|string|max:500',
            'comment_enabled' => 'nullable|in:0,1',
            'register_enabled' => 'nullable|in:0,1',
        ]);

        Setting::set('admin_name', $request->admin_name ?? '');
        Setting::set('site_title', $request->site_title ?? '');
        Setting::set('seo_keywords', $request->seo_keywords ?? '');
        Setting::set('seo_description', $request->seo_description ?? '');
        Setting::set('site_icp', $request->site_icp ?? '');
        Setting::set('site_contact', $request->site_contact ?? '');
        Setting::set('comment_enabled', $request->comment_enabled ?? '1');
        Setting::set('register_enabled', $request->register_enabled ?? '1');

        if ($request->hasFile('site_logo')) {
            $oldLogo = Setting::get('site_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('site_logo')->store(config('admin.upload_path', 'uploads'), 'public');
            Setting::set('site_logo', $path);
        }

        AdminOperationLog::log('更新系统设置', '系统设置');

        return back()->with('success', '设置已更新');
    }
}
