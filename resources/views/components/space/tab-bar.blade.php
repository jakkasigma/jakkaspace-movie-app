@props(['active' => ''])

<nav class="space-tab-bar" aria-label="Navigasi space">
    <a href="{{ route('your-space') }}" class="space-tab-item {{ $active === 'index' ? 'active' : '' }}">
        <svg class="space-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        <span class="space-tab-label">Space</span>
    </a>
    <a href="{{ route('your-space.analytics') }}" class="space-tab-item {{ $active === 'analytics' ? 'active' : '' }}">
        <svg class="space-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        <span class="space-tab-label">Statistik</span>
    </a>
    <a href="{{ route('your-space.diary') }}" class="space-tab-item {{ $active === 'diary' ? 'active' : '' }}">
        <svg class="space-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        <span class="space-tab-label">Diary</span>
    </a>
    <a href="{{ route('your-space.history') }}" class="space-tab-item {{ $active === 'history' ? 'active' : '' }}">
        <svg class="space-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>
        <span class="space-tab-label">History</span>
    </a>
    <a href="{{ route('your-space.watchlist') }}" class="space-tab-item {{ $active === 'watchlist' ? 'active' : '' }}">
        <svg class="space-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
        </svg>
        <span class="space-tab-label">Watchlist</span>
    </a>
    <a href="{{ route('your-space.favorites') }}" class="space-tab-item {{ $active === 'favorites' ? 'active' : '' }}">
        <svg class="space-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
        </svg>
        <span class="space-tab-label">Favorit</span>
    </a>
    <a href="{{ route('your-space.lists') }}" class="space-tab-item {{ $active === 'lists' ? 'active' : '' }}">
        <svg class="space-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="8" y1="6" x2="21" y2="6"/>
            <line x1="8" y1="12" x2="21" y2="12"/>
            <line x1="8" y1="18" x2="21" y2="18"/>
            <line x1="3" y1="6" x2="3.01" y2="6"/>
            <line x1="3" y1="12" x2="3.01" y2="12"/>
            <line x1="3" y1="18" x2="3.01" y2="18"/>
        </svg>
        <span class="space-tab-label">Lists</span>
    </a>
</nav>
