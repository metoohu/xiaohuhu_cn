@extends('front.layouts.master')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10 md:py-14">
    <div class="bg-white rounded-2xl border border-haze-200 p-6 md:p-8 shadow-sm">
        <h1 class="text-xl font-serif font-semibold text-primary-800 mb-2">我的表情包</h1>
        <p class="text-sm text-dark-800/60 mb-6">上传的图片可在文章评论区通过「我的表情」插入。单张不超过 {{ config('front.stickers.max_kb', 512) }}KB，最多 {{ config('front.stickers.max_per_user', 50) }} 个。</p>

        @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
        @endif

        <form action="{{ route('front.my.stickers.store') }}" method="POST" enctype="multipart/form-data" class="mb-8 pb-8 border-b border-haze-200">
            @csrf
            <label class="block text-sm font-medium text-dark-800/80 mb-2">上传新表情</label>
            <div class="flex flex-wrap items-center gap-3">
                <input type="file" name="image" accept="image/*" required class="text-sm text-dark-800/80">
                <button type="submit" class="px-5 py-2.5 bg-primary-500 text-white rounded-xl hover:bg-primary-600 text-sm font-medium">上传</button>
            </div>
            @error('image')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>

        @if($stickers->isEmpty())
        <p class="text-dark-800/50 text-sm">暂无表情包，上传后即可在评论中使用。</p>
        @else
        <ul class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
            @foreach($stickers as $s)
            <li class="relative group rounded-xl border border-haze-200 p-2 bg-haze-50/50">
                <img src="{{ Storage::url($s->image_path) }}" alt="" class="w-full h-14 object-contain">
                <form action="{{ route('front.my.stickers.destroy', $s) }}" method="POST" class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl"
                      onsubmit="return confirm('确定删除该表情？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-white bg-red-600 px-2 py-1 rounded">删除</button>
                </form>
            </li>
            @endforeach
        </ul>
        @endif

        <p class="mt-8 flex flex-wrap gap-4">
            <a href="{{ route('front.my.profile') }}" class="text-primary-600 hover:text-primary-700 text-sm">← 返回个人中心</a>
            <a href="{{ route('front.home') }}" class="text-dark-800/50 hover:text-primary-600 text-sm">首页</a>
        </p>
    </div>
</div>
@endsection
