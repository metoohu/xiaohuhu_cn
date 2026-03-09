<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>忘记密码 - {{ \App\Models\Setting::adminName() }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    @endif
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-center mb-6">忘记密码</h1>
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded text-sm break-all">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">@foreach ($errors->all() as $e) {{ $e }} @endforeach</div>
            @endif
            <form method="POST" action="{{ route('admin.forgot-password') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">邮箱</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full py-2 px-4 bg-slate-800 text-white rounded hover:bg-slate-700">发送重置链接</button>
            </form>
            <a href="{{ route('admin.login') }}" class="block mt-4 text-center text-sm text-blue-600 hover:underline">返回登录</a>
        </div>
    </div>
</body>
</html>
