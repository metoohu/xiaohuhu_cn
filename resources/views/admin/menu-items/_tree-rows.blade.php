@foreach($items as $item)
<tr class="hover:bg-slate-50/90 transition-colors">
    <td class="py-3 px-3 align-top" style="padding-left: {{ 0.75 + $depth * 1.25 }}rem">
        @if($item->is_divider)
        <span class="text-slate-400 text-xs uppercase tracking-wide">— 分隔线 —</span>
        @else
        <span class="font-medium text-slate-800">{{ $item->title ?: '（无标题）' }}</span>
        @endif
    </td>
    <td class="py-3 px-3 text-slate-600 text-xs align-top">
        @if($item->is_divider)
        <span class="text-slate-300">—</span>
        @elseif($item->route_name)
        <code class="text-[11px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 break-all">{{ $item->route_name }}</code>
        @else
        <span class="text-slate-400">—</span>
        @endif
    </td>
    <td class="py-3 px-3 text-slate-600 text-xs max-w-[12rem] truncate align-top" title="{{ $item->url }}">{{ $item->url ?: '—' }}</td>
    <td class="py-3 px-3 text-slate-600 text-xs align-top break-all max-w-[10rem]">{{ $item->active_pattern ?: '—' }}</td>
    <td class="py-3 px-3 text-slate-700 tabular-nums align-top">{{ $item->sort }}</td>
    <td class="py-3 px-3 align-top">
        @if($item->is_active)
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80">显示</span>
        @else
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200">隐藏</span>
        @endif
    </td>
    <td class="py-3 px-3 align-top">
        <div class="admin-table-actions">
            @if(!$item->is_divider)
            <form action="{{ route('admin.menu-items.move-up', $item) }}" method="POST">
                @csrf
                <button type="submit" class="admin-btn-action admin-btn-action--neutral" title="同级上移">上移</button>
            </form>
            <form action="{{ route('admin.menu-items.move-down', $item) }}" method="POST">
                @csrf
                <button type="submit" class="admin-btn-action admin-btn-action--neutral" title="同级下移">下移</button>
            </form>
            <a href="{{ route('admin.menu-items.create', ['parent_id' => $item->id]) }}" class="admin-btn-action admin-btn-action--teal">子菜单</a>
            <a href="{{ route('admin.menu-items.edit', $item) }}" class="admin-btn-action admin-btn-action--primary">编辑</a>
            @endif
            <form action="{{ route('admin.menu-items.destroy', $item) }}" method="POST" onsubmit="return confirm('确定删除？子菜单将一并删除。');">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn-action admin-btn-action--danger">删除</button>
            </form>
        </div>
    </td>
</tr>
@if($item->children->isNotEmpty())
    @include('admin.menu-items._tree-rows', ['items' => $item->children, 'depth' => $depth + 1])
@endif
@endforeach
