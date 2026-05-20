@php
    $wordModel = $forbiddenWord ?? null;
    $selectedCategoryId = old('category_id', $wordModel?->category_id);
    $toneCategoryIds = $categories->where('level', \App\Models\ForbiddenWordCategory::LEVEL_TONE)->pluck('id')->all();
@endphp
<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium mb-1">分类 <span class="text-red-600">*</span></label>
        <select name="category_id" id="forbiddenWordCategory" required class="w-full rounded border-slate-300">
            <option value="">请选择</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}"
                    data-level="{{ $cat->level }}"
                    @selected((string) $selectedCategoryId === (string) $cat->id)>
                    {{ $cat->name }}（{{ $cat->level === 'block' ? '红线' : '调性' }}）
                </option>
            @endforeach
        </select>
        @error('category_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">违禁词 <span class="text-red-600">*</span></label>
        <input type="text" name="word" value="{{ old('word', $wordModel?->word) }}" required maxlength="100" class="w-full rounded border-slate-300">
        @error('word')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">匹配方式 <span class="text-red-600">*</span></label>
        <select name="match_type" class="w-full rounded border-slate-300">
            <option value="exact" @selected(old('match_type', $wordModel?->match_type ?? 'exact') === 'exact')>精确匹配</option>
            <option value="fuzzy" @selected(old('match_type', $wordModel?->match_type) === 'fuzzy')>模糊匹配（* 通配）</option>
        </select>
        @error('match_type')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div id="replacementField">
        <label class="block text-sm font-medium mb-1">替换词 <span id="replacementRequiredMark" class="text-red-600 hidden">*</span></label>
        <input type="text" name="replacement" id="replacementInput" value="{{ old('replacement', $wordModel?->replacement) }}" maxlength="100" class="w-full rounded border-slate-300">
        <p class="text-xs text-slate-500 mt-1">调性违规类必填；正文 1～2 处命中时将自动替换为此词。</p>
        @error('replacement')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">启用状态</label>
        <label class="inline-flex items-center gap-2 mr-4">
            <input type="radio" name="is_enabled" value="1" @checked(old('is_enabled', $wordModel?->is_enabled ?? true) == true || old('is_enabled') === '1')> 启用
        </label>
        <label class="inline-flex items-center gap-2">
            <input type="radio" name="is_enabled" value="0" @checked(old('is_enabled', $wordModel?->is_enabled ?? true) == false || old('is_enabled') === '0')> 禁用
        </label>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">备注</label>
        <input type="text" name="remark" value="{{ old('remark', $wordModel?->remark) }}" maxlength="255" class="w-full rounded border-slate-300">
        @error('remark')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
@push('scripts')
<script>
(function () {
    var select = document.getElementById('forbiddenWordCategory');
    var mark = document.getElementById('replacementRequiredMark');
    var input = document.getElementById('replacementInput');
    var toneIds = @json($toneCategoryIds);

    function syncReplacementRequired() {
        var id = parseInt(select.value, 10);
        var isTone = toneIds.indexOf(id) !== -1;
        if (mark) mark.classList.toggle('hidden', !isTone);
        if (input) input.required = isTone;
    }

    if (select) {
        select.addEventListener('change', syncReplacementRequired);
        syncReplacementRequired();
    }
})();
</script>
@endpush
