@php
    use Illuminate\Support\Str;

    $navTree = ($navMenus ?? collect())
        ->where('is_active', true)
        ->whereNull('parent_id')
        ->sortBy('position');
@endphp

<div class="mobile-menu" id="mobileMenu">
    <ul>
        @foreach ($navTree as $item)
            @php
                $children = $item->children->where('is_active', true)->sortBy('position');
                $menuId = 'nav-' . $loop->index;
                $itemTitle = $item->title;
                $itemUrl = $item->type === 'custom' && $item->url ? $item->url : ($item->page_slug ? url($item->page_slug) : '#');
                
                if (auth()->check() && (Str::lower($itemTitle) === 'login' || Str::lower($itemTitle) === 'masuk')) {
                    $itemTitle = 'Dashboard';
                    $itemUrl = route('admin.dashboard');
                }
            @endphp
            <li>
                <a href="{{ $itemUrl }}" 
                   @if($children->count()) onclick="toggleMobileSubmenu(event, '{{ $menuId }}')" @endif
                   @if($item->target_blank) target="_blank" rel="noopener" @endif>
                    {{ $itemTitle }}
                    @if($children->count()) <i class="fas fa-chevron-down"></i> @endif
                </a>
                @if($children->count())
                    <ul class="mobile-submenu" id="{{ $menuId }}-submenu">
                        @foreach ($children as $child)
                            @php
                                $childTitle = $child->title;
                                $childUrl = $child->type === 'custom' && $child->url ? $child->url : ($child->page_slug ? url($child->page_slug) : '#');
                                if (auth()->check() && (Str::lower($childTitle) === 'login' || Str::lower($childTitle) === 'masuk')) {
                                    $childTitle = 'Dashboard';
                                    $childUrl = route('admin.dashboard');
                                }
                            @endphp
                            <li>
                                <a href="{{ $childUrl }}" @if($child->target_blank) target="_blank" rel="noopener" @endif>
                                    {{ $childTitle }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
        @isset($extraLinks)
            @foreach($extraLinks as $link)
                <li><a href="{{ $link['url'] }}">{{ $link['title'] }}</a></li>
            @endforeach
        @endisset
    </ul>
</div>
