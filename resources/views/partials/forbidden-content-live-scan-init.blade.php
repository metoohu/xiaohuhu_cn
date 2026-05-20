{{-- M8：绑定编辑页已有 DOM（#forbidden-title-preview / #forbidden-body-badge）并加载 scan JS --}}
@php
    $scanUrl = $scanUrl ?? '';
    $context = $context ?? 'article';
    $titleSelector = $titleSelector ?? 'input[name="title"]';
    $bodySelector = $bodySelector ?? '#article-content';
    $bodyTinymce = ! empty($bodyTinymce);
    $tagsSelector = $tagsSelector ?? null;
    $contentField = $contentField ?? 'content';
@endphp
<div
    class="hidden"
    data-forbidden-legacy-scan
    data-scan-url="{{ $scanUrl }}"
    data-context="{{ $context }}"
    data-title-selector="{{ $titleSelector }}"
    data-body-selector="{{ $bodySelector }}"
    data-body-tinymce="{{ $bodyTinymce ? '1' : '0' }}"
    data-content-field="{{ $contentField }}"
    @if ($tagsSelector) data-tags-selector="{{ $tagsSelector }}" @endif
></div>

@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/js/forbidden-content-scan.js'])
@else
    {{-- 无 Vite 构建时内联加载（前台 CDN 布局、后台未 npm run build） --}}
    <script>{!! file_get_contents(resource_path('js/forbidden-content-scan.js')) !!}</script>
@endif
