@props(['active' => ''])

<nav class="space-nav">
    <a href="{{ route('your-space') }}" class="space-nav-link {{ $active === 'index' ? 'active' : '' }}">
        Ringkasan
    </a>
    <a href="{{ route('your-space.analytics') }}" class="space-nav-link {{ $active === 'analytics' ? 'active' : '' }}">
        Statistik
    </a>
    <a href="{{ route('your-space.diary') }}" class="space-nav-link {{ $active === 'diary' ? 'active' : '' }}">
        Diary
    </a>
    <a href="{{ route('your-space.history') }}" class="space-nav-link {{ $active === 'history' ? 'active' : '' }}">
        History
    </a>
    <a href="{{ route('your-space.watchlist') }}" class="space-nav-link {{ $active === 'watchlist' ? 'active' : '' }}">
        Watchlist
    </a>
    <a href="{{ route('your-space.favorites') }}" class="space-nav-link {{ $active === 'favorites' ? 'active' : '' }}">
        Favorit
    </a>
    <a href="{{ route('your-space.lists') }}" class="space-nav-link {{ $active === 'lists' ? 'active' : '' }}">
        Lists
    </a>

    <div class="space-nav-spacer"></div>

    @if (auth()->user()?->username)
        <a href="{{ route('profile.show', auth()->user()->username) }}" class="space-nav-link {{ $active === 'profile' ? 'active' : '' }}">
            Profil Publik
        </a>
    @endif
    <a href="{{ route('profile.edit') }}" class="space-nav-link {{ $active === 'settings' ? 'active' : '' }}">
        Pengaturan
    </a>
</nav>
