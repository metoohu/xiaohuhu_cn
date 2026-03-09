<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登录 - {{ \App\Models\Setting::adminName() }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    @endif
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-center mb-6">{{ \App\Models\Setting::adminName() }}</h1>

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">
                    @foreach ($errors->all() as $e) {{ $e }} @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">邮箱</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">密码</label>
                        <input type="password" name="password" required
                               class="w-full rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    @if (config('captcha'))
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">验证码</label>
                        <div class="flex gap-2">
                            <input type="text" name="captcha" required
                                   class="flex-1 rounded border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <img src="{{ route('admin.captcha') }}" alt="captcha" onclick="this.src='{{ route('admin.captcha') }}?'+Math.random()" class="h-10 cursor-pointer rounded">
                        </div>
                    </div>
                    @endif
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" class="rounded border-slate-300">
                        <label for="remember" class="ml-2 text-sm text-slate-600">记住登录</label>
                    </div>
                </div>
                <div class="mt-6 space-y-2">
                    <button type="submit" class="w-full py-2 px-4 bg-slate-800 text-white rounded hover:bg-slate-700">登录</button>
                    <a href="{{ route('admin.forgot-password') }}" class="block text-center text-sm text-blue-600 hover:underline">忘记密码？</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
