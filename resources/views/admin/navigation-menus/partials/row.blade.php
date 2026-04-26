@php
    $padding = $level * 16;
    $link = $menu->type === 'custom' ? ($menu->url ?: '-') : ($menu->page_slug ?: '-');
    $levelClass = $level === 0
        ? 'nav-title-parent'
        : ($level === 1 ? 'nav-title-child nav-title-child--lvl2' : ($level === 2 ? 'nav-title-child nav-title-child--lvl3' : 'nav-title-child nav-title-child--lvl4'));
@endphp
<tr class="{{ $level === 0 ? 'nav-parent-row' : 'nav-child-row' }}">
    <td>
        <div style="padding-left: {{ $padding }}px; display:flex; align-items:center; gap:8px;">
            @if($menu->icon)
                <i class="{{ $menu->icon }}"></i>
            @endif
            <span class="{{ $levelClass }}">{{ $menu->title }}</span>
        </div>
    </td>
    <td><span class="badge badge--muted">{{ $link }}</span></td>
    <td><span class="status-pill status-pill--{{ $menu->is_active ? 'published' : 'draft' }}">{{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
    <td>{{ $menu->position }}</td>
    <td class="table-actions">
        <details class="news-table__action-dropdown" data-action-menu>
            <summary
                class="action-button action-button--dots"
                aria-haspopup="menu"
                aria-expanded="false"
                aria-label="Tampilkan opsi untuk {{ $menu->title }}"
            >
                <i class="fas fa-ellipsis-v"></i>
            </summary>
            <div class="news-table__action-options" role="menu">
                <a
                    class="news-table__action-item"
                    href="{{ route('admin.navigation-menus.index', ['edit' => $menu->id]) }}"
                    role="menuitem"
                >
                    <i class="fas fa-pen"></i><span>Edit</span>
                </a>
                <button
                    class="news-table__action-item news-table__action-item--danger delete-trigger"
                    type="button"
                    role="menuitem"
                    data-nav-delete="{{ route('admin.navigation-menus.destroy', $menu) }}"
                    data-nav-title="Hapus menu: {{ $menu->title }}?"
                >
                    <i class="fas fa-trash"></i><span>Hapus</span>
                </button>
            </div>
        </details>
    </td>
</tr>
@foreach($menu->children as $child)
    @include('admin.navigation-menus.partials.row', ['menu' => $child, 'level' => $level + 1])
@endforeach
