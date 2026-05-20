{{-- 提交后违禁词拦截汇总（M6-3，session forbidden_scan） --}}
@php
    $scan = session('forbidden_scan');
@endphp
@if (is_array($scan) && ! empty($scan['messages']))
<div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-900">
    <p class="font-medium mb-2">内容未通过违禁词校验</p>
    <ul class="list-disc list-inside text-sm space-y-1">
        @foreach ($scan['messages'] as $msg)
            <li>{{ $msg }}</li>
        @endforeach
    </ul>
    @if (! empty($scan['hits']))
    <table class="mt-3 w-full text-sm border-collapse">
        <thead>
            <tr class="text-left border-b border-red-200">
                <th class="py-1 pr-2">字段</th>
                <th class="py-1 pr-2">命中词</th>
                <th class="py-1">分类</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($scan['hits'] as $hit)
            <tr class="border-b border-red-100">
                <td class="py-1 pr-2">{{ $hit['field'] ?? '' }}</td>
                <td class="py-1 pr-2">{{ $hit['word'] ?? '' }}</td>
                <td class="py-1">{{ $hit['category_name'] ?? $hit['category_slug'] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif
