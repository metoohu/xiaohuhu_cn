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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --heal-mint: #a8e6cf;
            --heal-sage: #88d8b0;
            --heal-cream: #fef9f3;
            --heal-lavender: #e8d5e7;
            --heal-peach: #fce4b4;
            --heal-teal: #6b9b8a;
            --heal-text: #4a6b5c;
            --heal-text-light: #7a9b8c;
        }
        body { font-family: 'Noto Sans SC', sans-serif; }
        .login-bg {
            background: linear-gradient(135deg, #a8e6cf 0%, #d4e9e2 25%, #fef9f3 50%, #fce4b4 75%, #e8d5e7 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .login-card-3d {
            transform-style: preserve-3d;
            perspective: 1000px;
            transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .login-card-3d:hover {
            transform: rotateX(2deg) rotateY(-2deg) translateZ(20px);
        }
        .login-card-inner {
            background: linear-gradient(145deg, rgba(255,255,255,0.95) 0%, rgba(254,249,243,0.9) 100%);
            backdrop-filter: blur(20px);
            box-shadow:
                0 25px 50px -12px rgba(107, 155, 138, 0.25),
                0 0 0 1px rgba(255,255,255,0.5),
                inset 0 1px 0 rgba(255,255,255,0.8);
        }
        .login-card-inner::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 1.5rem;
            background: linear-gradient(135deg, rgba(168,230,207,0.3), rgba(232,213,231,0.2));
            z-index: -1;
            filter: blur(8px);
            opacity: 0.6;
        }
        .input-heal {
            background: rgba(255,255,255,0.8);
            border: 1px solid rgba(107,155,138,0.2);
            transition: all 0.3s ease;
        }
        .input-heal:focus {
            background: rgba(255,255,255,1);
            border-color: var(--heal-teal);
            box-shadow: 0 0 0 3px rgba(107,155,138,0.15);
        }
        .btn-heal {
            background: linear-gradient(135deg, #6b9b8a 0%, #5a8a79 100%);
            box-shadow: 0 4px 15px rgba(107,155,138,0.4);
            transition: all 0.3s ease;
        }
        .btn-heal:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107,155,138,0.5);
        }
        .float-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.4;
            animation: float 8s ease-in-out infinite;
        }
        .float-shape-1 { width: 300px; height: 300px; background: var(--heal-mint); top: 10%; left: 5%; animation-delay: 0s; }
        .float-shape-2 { width: 250px; height: 250px; background: var(--heal-lavender); top: 60%; right: 10%; animation-delay: -2s; }
        .float-shape-3 { width: 200px; height: 200px; background: var(--heal-peach); bottom: 15%; left: 20%; animation-delay: -4s; }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(20px, -20px) scale(1.05); }
            66% { transform: translate(-15px, 10px) scale(0.95); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 overflow-hidden login-bg relative">
    <div class="float-shape float-shape-1"></div>
    <div class="float-shape float-shape-2"></div>
    <div class="float-shape float-shape-3"></div>

    <div class="w-full max-w-md relative z-10">
        <div class="login-card-3d">
            <div class="login-card-inner relative rounded-2xl p-8">
                <h1 class="text-2xl font-semibold text-center mb-6 text-[var(--heal-text)] tracking-wide">{{ \App\Models\Setting::adminName() }}</h1>

                @if (session('status'))
                    <div class="mb-4 p-3 rounded-xl text-sm" style="background: rgba(168,230,207,0.4); color: #2d5a4a;">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-xl text-sm" style="background: rgba(248,181,181,0.5); color: #8b3a3a;">
                        @foreach ($errors->all() as $e) {{ $e }} @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-[var(--heal-text)]">邮箱</label>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                   class="input-heal w-full rounded-xl py-2.5 px-4">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-[var(--heal-text)]">密码</label>
                            <input type="password" name="password" required
                                   class="input-heal w-full rounded-xl py-2.5 px-4">
                        </div>
                        @if (config('captcha'))
                        <div>
                            <label class="block text-sm font-medium mb-1 text-[var(--heal-text)]">验证码</label>
                            <div class="flex gap-2">
                                <input type="text" name="captcha" required
                                       class="input-heal flex-1 rounded-xl py-2.5 px-4">
                                <img src="{{ route('admin.captcha') }}" alt="captcha" onclick="this.src='{{ route('admin.captcha') }}?'+Math.random()" class="h-10 cursor-pointer rounded-xl border border-[rgba(107,155,138,0.2)]">
                            </div>
                        </div>
                        @endif
                        <div class="flex items-center">
                            <input type="checkbox" name="remember" id="remember" class="rounded border-[var(--heal-teal)] text-[var(--heal-teal)] focus:ring-[var(--heal-teal)]">
                            <label for="remember" class="ml-2 text-sm text-[var(--heal-text-light)]">记住登录</label>
                        </div>
                    </div>
                    <div class="mt-6 space-y-2">
                        <button type="submit" class="btn-heal w-full py-3 px-4 text-white rounded-xl font-medium">登录</button>
                        <a href="{{ route('admin.forgot-password') }}" class="block text-center text-sm hover:underline transition" style="color: var(--heal-teal);">忘记密码？</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
