@php
    $depthVal = $depth ?? 0;
@endphp
<ul class="@if($depthVal > 0) admin-sidebar-nested mt-0.5 space-y-0.5 @else admin-sidebar-root space-y-0.5 @endif" role="list">
@foreach($items as $item)
    @if($item->is_divider)
        <li class="admin-nav-item list-none" role="separator" data-nav-id="div-{{ $item->id }}" aria-hidden="true">
            <div class="admin-nav-hr mx-2 my-2 border-0 border-t"></div>
        </li>
    @else
        @php
            $href = $item->resolveHref();
            $active = $item->isRouteActive();
            $pl = 0.75 + $depthVal * 0.75;
        @endphp
        <li class="admin-nav-item list-none" data-nav-id="m-{{ $item->id }}">
            @if($href)
                <a href="{{ $href }}"
                   class="admin-sidebar-link block rounded-md text-[0.9375rem] leading-snug transition-[background-color,color,box-shadow] duration-200 ease-out {{ $active ? 'nav-active' : '' }}"
                   style="padding: 0.5rem 0.75rem 0.5rem {{ $pl }}rem"
                   @if($active) aria-current="page" @endif>{{ $item->title }}</a>
            @elseif($item->title !== '')
                <span class="admin-sidebar-group block px-3 py-1.5 text-[0.6875rem] font-semibold uppercase tracking-wider" style="padding-left: {{ $pl }}rem">{{ $item->title }}</span>
            @endif
            @if($item->children->isNotEmpty())
                @include('admin.partials.sidebar-nav', ['items' => $item->children, 'depth' => $depthVal + 1])
            @endif
        </li>
    @endif
@endforeach
</ul>
