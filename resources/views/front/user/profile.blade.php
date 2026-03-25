@extends('front.layouts.master')

@php
    $initialTab = 'showcase';
    if ($errors->any()) {
        foreach (['birthday', 'gender', 'interests', 'occupation'] as $field) {
            if ($errors->has($field)) {
                $initialTab = 'profile';
                break;
            }
        }
    }
@endphp

@section('content')
<div class="max-w-lg mx-auto pb-16 md:pb-20 -mx-4 sm:mx-auto sm:px-0 min-h-[60vh]" x-data="{ tab: @js($initialTab), moodPicker: false }">
    <div class="bg-[#ededed] px-3 pt-4 pb-6 sm:rounded-none sm:px-3">

        <form action="{{ route('front.my.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-0">
            @csrf
            @method('PUT')

            {{-- 頂部：頭像 + 暱稱（各分頁共用） --}}
            <div class="rounded-xl overflow-hidden bg-white shadow-sm shadow-black/[0.03] mb-3">
                <div class="flex items-center gap-4 px-4 py-5">
                    <label for="profile_avatar" class="relative shrink-0 cursor-pointer group">
                        @if($user->avatar)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar) }}" alt="" class="w-[72px] h-[72px] rounded-lg object-cover border border-[#eee]">
                        @else
                        <div class="w-[72px] h-[72px] rounded-lg bg-[#e8e8e8] flex items-center justify-center text-primary-600 text-2xl font-medium border border-[#eee]">{{ mb_substr($user->name, 0, 1) }}</div>
                        @endif
                        <span class="absolute inset-0 rounded-lg bg-black/0 group-hover:bg-black/10 transition-colors flex items-end justify-center pb-1">
                            <span class="text-[10px] text-white drop-shadow opacity-0 group-hover:opacity-100">更换</span>
                        </span>
                        <input type="file" name="avatar" id="profile_avatar" accept="image/*" class="sr-only">
                    </label>
                    <div class="min-w-0 flex-1">
                        <p class="text-[17px] font-medium text-[#111] truncate">{{ $user->name }}</p>
                        <p class="text-[13px] text-[#888] mt-0.5">点击左侧头像可更换 · 最大 2MB</p>
                    </div>
                </div>
                @error('avatar')
                <p class="px-4 pb-3 text-[13px] text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tab 欄 --}}
            <div class="flex rounded-xl bg-white shadow-sm shadow-black/[0.03] p-1 gap-0.5 mb-3" role="tablist">
                <button type="button" role="tab" :aria-selected="tab === 'showcase'"
                        @click="tab = 'showcase'"
                        :class="tab === 'showcase' ? 'bg-[#ededed] text-[#111] font-medium' : 'text-[#666] hover:bg-[#f7f7f7]'"
                        class="flex-1 py-2.5 text-[14px] rounded-lg transition-colors">
                    形象与心情
                </button>
                <button type="button" role="tab" :aria-selected="tab === 'profile'"
                        @click="tab = 'profile'"
                        :class="tab === 'profile' ? 'bg-[#ededed] text-[#111] font-medium' : 'text-[#666] hover:bg-[#f7f7f7]'"
                        class="flex-1 py-2.5 text-[14px] rounded-lg transition-colors">
                    详细资料
                </button>
                <button type="button" role="tab" :aria-selected="tab === 'more'"
                        @click="tab = 'more'"
                        :class="tab === 'more' ? 'bg-[#ededed] text-[#111] font-medium' : 'text-[#666] hover:bg-[#f7f7f7]'"
                        class="flex-1 py-2.5 text-[14px] rounded-lg transition-colors">
                    更多
                </button>
            </div>

            {{-- 分頁：形象与心情（驗證錯在詳細資料時預先隱藏，避免無 JS 時兩欄同現） --}}
            <div x-show="tab === 'showcase'" @if($initialTab === 'profile') x-cloak @endif x-transition.opacity.duration.150ms class="space-y-0">
                <p class="text-[13px] text-[#888] px-1 mb-1.5">个性签名与心情</p>
                <div class="rounded-xl overflow-hidden bg-white shadow-sm shadow-black/[0.03] divide-y divide-[#0000000d]">
                    <div class="px-4 py-3">
                        <label class="block text-[13px] text-[#888] mb-2">个性签名</label>
                        <textarea name="signature" rows="3" maxlength="500" class="w-full border-0 bg-transparent p-0 text-[15px] text-[#333] placeholder:text-[#bbb] focus:ring-0 resize-y min-h-[4.5rem]" placeholder="一句话介绍自己">{{ old('signature', $user->signature) }}</textarea>
                        @error('signature')
                        <p class="mt-2 text-[13px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="px-4 py-3">
                        <span class="block text-[13px] text-[#888] mb-2">心情</span>
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <input type="text" name="mood_emoji" id="mood_emoji_input" value="{{ old('mood_emoji', $user->mood_emoji) }}" maxlength="32"
                                   class="w-[4.5rem] text-center text-2xl rounded-md border border-[#e5e5e5] px-2 py-2 bg-[#fafafa]" placeholder="表情">
                            <button type="button" @click="moodPicker = !moodPicker" class="px-3 py-2 text-[14px] rounded-md border border-[#e5e5e5] bg-white text-[#576b95] active:bg-[#f0f0f0]">选择表情</button>
                            <button type="button" class="text-[13px] text-[#576b95]" @click="document.getElementById('mood_emoji_input').value = ''">清除</button>
                        </div>
                        <div x-show="moodPicker" x-cloak x-transition class="mb-3 p-3 rounded-lg border border-[#eee] bg-[#fafafa] max-h-36 overflow-y-auto">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','😉','😍','🥰','😘','🥺','😎','🤩','🥳','😢','😭','😤','🤔','😴','🤗','👍','👏','🙏','💪','❤️','💕','✨','🌈','🌸','🍀','☀️','🌙','☕','🎵','📖'] as $emo)
                                <button type="button" class="text-xl leading-none p-1 rounded hover:bg-white"
                                        @click="document.getElementById('mood_emoji_input').value = @js($emo); moodPicker = false">{{ $emo }}</button>
                                @endforeach
                            </div>
                        </div>
                        <input type="text" name="mood_text" value="{{ old('mood_text', $user->mood_text) }}" maxlength="120"
                               class="w-full border-0 border-t border-[#f0f0f0] pt-3 mt-1 bg-transparent text-[15px] text-[#333] placeholder:text-[#bbb] focus:ring-0" placeholder="心情文字（可选）">
                        @error('mood_emoji')
                        <p class="mt-2 text-[13px] text-red-600">{{ $message }}</p>
                        @enderror
                        @error('mood_text')
                        <p class="mt-2 text-[13px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 分頁：详细资料 --}}
            <div x-show="tab === 'profile'" @if($initialTab === 'showcase') x-cloak @endif x-transition.opacity.duration.150ms class="space-y-0">
                <p class="text-[13px] text-[#888] px-1 mb-1.5">个人资料</p>
                <div class="rounded-xl overflow-hidden bg-white shadow-sm shadow-black/[0.03] divide-y divide-[#0000000d]">
                    <div class="px-0">
                        <label class="flex items-center min-h-[52px] px-4 gap-3">
                            <span class="text-[15px] text-[#333] w-[4.5rem] shrink-0">生日</span>
                            <input type="date" name="birthday" value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                                   class="flex-1 min-w-0 border-0 bg-transparent text-[15px] text-[#333] text-right focus:ring-0 py-2">
                        </label>
                        @error('birthday')
                        <p class="px-4 pb-3 text-[13px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="px-0">
                        <div class="flex items-center min-h-[52px] px-4 gap-3">
                            <span class="text-[15px] text-[#333] w-[4.5rem] shrink-0">性别</span>
                            <select name="gender" class="flex-1 min-w-0 border-0 bg-transparent text-[15px] text-[#333] text-right focus:ring-0 py-2 pr-0 cursor-pointer">
                                <option value="">不选择</option>
                                <option value="male" @selected(old('gender', $user->gender) === 'male')>男</option>
                                <option value="female" @selected(old('gender', $user->gender) === 'female')>女</option>
                                <option value="other" @selected(old('gender', $user->gender) === 'other')>其他</option>
                                <option value="secret" @selected(old('gender', $user->gender) === 'secret')>不公开</option>
                            </select>
                        </div>
                        @error('gender')
                        <p class="px-4 pb-3 text-[13px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="px-4 py-3">
                        <label class="block text-[13px] text-[#888] mb-2">兴趣爱好</label>
                        <textarea name="interests" rows="3" maxlength="500" class="w-full border-0 bg-transparent p-0 text-[15px] text-[#333] placeholder:text-[#bbb] focus:ring-0 resize-y min-h-[4rem]" placeholder="例如：阅读、摄影、跑步">{{ old('interests', $user->interests) }}</textarea>
                        @error('interests')
                        <p class="mt-2 text-[13px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="px-0">
                        <label class="flex items-center min-h-[52px] px-4 gap-3">
                            <span class="text-[15px] text-[#333] w-[4.5rem] shrink-0">职业</span>
                            <input type="text" name="occupation" value="{{ old('occupation', $user->occupation) }}" maxlength="100"
                                   class="flex-1 min-w-0 border-0 bg-transparent text-[15px] text-[#333] text-right placeholder:text-[#bbb] focus:ring-0 py-2" placeholder="选填">
                        </label>
                        @error('occupation')
                        <p class="px-4 pb-3 text-[13px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="px-1 mt-6" x-show="tab !== 'more'" x-transition.opacity.duration.150ms>
                <button type="submit" class="w-full py-3 rounded-xl bg-primary-500 text-white text-[16px] font-medium active:opacity-90 hover:bg-primary-600 transition-colors shadow-sm">
                    保存
                </button>
            </div>
        </form>

        {{-- 「更多」：表單外（獨立退出表單） --}}
        <div x-show="tab === 'more'" x-cloak x-transition.opacity.duration.150ms class="mt-3 space-y-3">
            <p class="text-[13px] text-[#888] px-1">账号与其他</p>
            <div class="rounded-xl overflow-hidden bg-white shadow-sm shadow-black/[0.03] divide-y divide-[#0000000d]">
                <a href="{{ route('front.my.stickers') }}" class="flex items-center justify-between min-h-[52px] px-4 text-[15px] text-[#333] active:bg-[#f5f5f5]">
                    <span>我的表情</span>
                    <span class="text-[#c7c7cc] text-lg leading-none font-light">›</span>
                </a>
                <a href="{{ route('front.home') }}" class="flex items-center justify-between min-h-[52px] px-4 text-[15px] text-[#333] active:bg-[#f5f5f5]">
                    <span>返回首页</span>
                    <span class="text-[#c7c7cc] text-lg leading-none font-light">›</span>
                </a>
            </div>

            <div class="rounded-xl overflow-hidden bg-white shadow-sm shadow-black/[0.03]">
                <form action="{{ route('front.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-3.5 text-[15px] text-[#fa5151] active:bg-[#f5f5f5] font-medium">
                        退出登录
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-[12px] text-[#b2b2b2] mt-6 px-4" x-show="tab !== 'more'" x-transition.opacity.duration.150ms>评论中展示的头像、签名与心情可在「形象与心情」中修改并保存</p>
    </div>
</div>
@endsection
