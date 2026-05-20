@extends('admin.layouts.master')

@section('title', '编辑违禁词 - ' . \App\Models\Setting::adminName())

@section('content')
<div class="bg-white rounded-lg shadow p-4 max-w-lg">
    <h2 class="text-xl font-bold mb-4">编辑违禁词 #{{ $forbiddenWord->id }}</h2>
    <form method="POST" action="{{ route('admin.forbidden-words.update', $forbiddenWord) }}">
        @csrf
        @method('PUT')
        @include('admin.forbidden-words._form', ['categories' => $categories, 'forbiddenWord' => $forbiddenWord])
        <div class="mt-6 flex gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700">更新</button>
            <a href="{{ route('admin.forbidden-words.index') }}" class="px-4 py-2 bg-slate-200 rounded hover:bg-slate-300">取消</a>
        </div>
    </form>
</div>
@endsection
