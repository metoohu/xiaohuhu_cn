{{-- 违禁词实时扫描 UI（M8）：标题下方预览高亮 + 正文字段旁违规计数 --}}
@props([
    'scanUrl',
    'context' => 'article',
    'titleName' => 'title',
    'contentId' => 'article-content',
    'contentField' => 'content',
])

<div
    data-forbidden-scan
    data-scan-url="{{ $scanUrl }}"
    data-context="{{ $context }}"
    data-title-name="{{ $titleName }}"
    data-content-id="{{ $contentId }}"
    data-content-field="{{ $contentField }}"
    class="forbidden-content-scan mb-2"
>
    <p data-forbidden-title-preview class="hidden text-sm text-slate-700 leading-relaxed border border-red-100 bg-red-50/50 rounded px-2 py-1.5 mt-1"></p>
    <p data-forbidden-badge class="hidden text-xs font-medium mt-1" role="status">
        违规 <span data-forbidden-count>0</span> 处（正文以服务端校验为准）
    </p>
</div>

@push('scripts')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/js/forbidden-content-scan.js'])
    @else
        {{-- 无 Vite 构建时回退：将 scan 逻辑内联加载（开发环境 CDN Tailwind 路径） --}}
        <script src="{{ asset('js/forbidden-content-scan.js') }}" defer></script>
    @endif
@endpush
