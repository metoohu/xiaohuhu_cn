@extends('front.layouts.master')

@section('title', '注册 - ' . (\App\Models\Setting::adminName() ?: '小糊涂人生馆'))

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-lg border border-haze-200">
            <div class="p-8 md:p-10">
                <h1 class="text-2xl font-serif font-semibold text-primary-800 text-center mb-2">加入我们</h1>
                <p class="text-dark-800/60 text-sm text-center mb-8">在喧嚣中寻一方宁静，用文字温暖你我</p>

                @if (session('error'))
                    <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-xl text-sm">
                        @foreach ($errors->all() as $e) {{ $e }} @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('front.register') }}">
                    @csrf
                    @if($returnUrl ?? null)
                    <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                    @endif
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-dark-800 mb-1.5">昵称</label>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                                   class="w-full rounded-xl border border-haze-200 px-4 py-3 focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 outline-none transition"
                                   placeholder="请输入昵称">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-800 mb-1.5">邮箱</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full rounded-xl border border-haze-200 px-4 py-3 focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 outline-none transition"
                                   placeholder="用于登录">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-800 mb-1.5">密码</label>
                            <input type="password" name="password" required minlength="6"
                                   class="w-full rounded-xl border border-haze-200 px-4 py-3 focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 outline-none transition"
                                   placeholder="至少 6 位">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-800 mb-1.5">确认密码</label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full rounded-xl border border-haze-200 px-4 py-3 focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 outline-none transition"
                                   placeholder="再次输入密码">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-dark-800 mb-1.5">验证码</label>
                            <div class="flex items-center gap-3">
                                <input type="text" name="captcha" required autocomplete="off"
                                       class="flex-1 min-w-0 rounded-xl border border-haze-200 px-4 py-3 focus:ring-2 focus:ring-primary-500/30 focus:border-primary-400 outline-none transition"
                                       placeholder="请输入验证码">
                                <img src="{{ route('front.captcha') }}" alt="验证码" title="点击刷新"
                                     onclick="this.src='{{ route('front.captcha') }}?'+Date.now()"
                                     width="120" height="40"
                                     class="flex-shrink-0 rounded-lg cursor-pointer border border-haze-200 bg-haze-50">
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 space-y-3">
                        <button type="submit" class="w-full py-3 px-4 bg-primary-500 text-white rounded-xl hover:bg-primary-600 font-medium transition-colors">
                            注册
                        </button>
                        <p class="text-center text-sm text-dark-800/60">
                            已有账号？<a href="{{ route('front.login', array_filter(['return_url' => $returnUrl ?? null])) }}" class="text-primary-600 hover:text-primary-700 font-medium">立即登录</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
