@foreach($items as $item)
    @if($item->is_divider)
        <div class="admin-nav-item" data-nav-id="div-{{ $item->id }}" draggable="false" aria-hidden="true">
            <div class="border-t border-slate-300/45 my-2"></div>
        </div>
    @else
        @php
            $href = $item->resolveHref();
            $active = $item->isRouteActive();
            $depthVal = $depth ?? 0;
            $pl = 0.75 + $depthVal * 0.75;
        @endphp
        <div class="admin-nav-item" data-nav-id="m-{{ $item->id }}" draggable="false">
            @if($href)
                <a href="{{ $href }}" class="block px-3 py-2 rounded {{ $active ? 'nav-active' : '' }}" style="padding-left: {{ $pl }}rem">{{ $item->title }}</a>
            @elseif($item->title !== '')
                <span class="block px-3 py-2 text-xs font-medium text-slate-500" style="padding-left: {{ $pl }}rem">{{ $item->title }}</span>
            @endif
            @if($item->children->isNotEmpty())
                @include('admin.partials.sidebar-nav', ['items' => $item->children, 'depth' => $depthVal + 1])
            @endif
        </div>
    @endif
@endforeach
