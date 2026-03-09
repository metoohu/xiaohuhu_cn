<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminOperationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(): View
    {
        return view('admin.logs.index');
    }

    public function operation(Request $request): View
    {
        $logs = AdminOperationLog::query()
            ->with('adminUser')
            ->when($request->module, fn ($q) => $q->where('module', $request->module))
            ->when($request->keyword, fn ($q) => $q->where('action', 'like', "%{$request->keyword}%"))
            ->latest()
            ->paginate(config('admin.per_page', 20));

        return view('admin.logs.operation', compact('logs'));
    }

    public function error(Request $request): View
    {
        $logPath = storage_path('logs/laravel.log');
        $content = '';

        if (File::exists($logPath)) {
            $content = File::get($logPath);
            $lines = explode("\n", $content);
            $lines = array_slice($lines, -500);
            $content = implode("\n", $lines);
        }

        return view('admin.logs.error', compact('content'));
    }
}
