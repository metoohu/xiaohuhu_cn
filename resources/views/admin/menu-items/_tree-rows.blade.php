@foreach($items as $item)
<tr class="border-b border-slate-100 hover:bg-slate-50/80">
    <td class="py-2 pr-2 align-top" style="padding-left: {{ 0.75 + $depth * 1.25 }}rem">
        @if($item->is_divider)
        <span class="text-slate-400">— 分隔线 —</span>
        @else
        <span class="font-medium text-slate-800">{{ $item->title ?: '（无标题）' }}</span>
        @endif
    </td>
    <td class="py-2 pr-2 text-slate-600 text-xs">
        @if($item->is_divider)
        —
        @else
        {{ $item->route_name ?: '—' }}
        @endif
    </td>
    <td class="py-2 pr-2 text-slate-600 text-xs max-w-[10rem] truncate" title="{{ $item->url }}">{{ $item->url ?: '—' }}</td>
    <td class="py-2 pr-2 text-slate-600 text-xs">{{ $item->active_pattern ?: '—' }}</td>
    <td class="py-2 pr-2">{{ $item->sort }}</td>
    <td class="py-2 pr-2">
        @if($item->is_active)
        <span class="text-green-600">显示</span>
        @else
        <span class="text-slate-400">隐藏</span>
        @endif
    </td>
    <td class="py-2">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
            @if(!$item->is_divider)
            <form action="{{ route('admin.menu-items.move-up', $item) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-slate-600 hover:underline" title="同级上移">上移</button>
            </form>
            <form action="{{ route('admin.menu-items.move-down', $item) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-slate-600 hover:underline" title="同级下移">下移</button>
            </form>
            <a href="{{ route('admin.menu-items.create', ['parent_id' => $item->id]) }}" class="text-teal-600 hover:underline">子菜单</a>
            <a href="{{ route('admin.menu-items.edit', $item) }}" class="text-blue-600 hover:underline">编辑</a>
            @endif
            <form action="{{ route('admin.menu-items.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('确定删除？子菜单将一并删除。');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline">删除</button>
            </form>
        </div>
    </td>
</tr>
@if($item->children->isNotEmpty())
    @include('admin.menu-items._tree-rows', ['items' => $item->children, 'depth' => $depth + 1])
@endif
@endforeach
