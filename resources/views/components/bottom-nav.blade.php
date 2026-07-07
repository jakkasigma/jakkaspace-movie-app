<nav class="bottom-nav" aria-label="Navigasi utama">

    {{-- Home --}}
    <a
        href="{{ route('movies.index') }}"
        class="bottom-nav-item {{ request()->routeIs('movies.index') ? 'active' : '' }}"
        aria-label="Home"
    >
        <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span class="bottom-nav-label">Home</span>
    </a>

    {{-- Search --}}
    <a
        href="{{ route('search') }}"
        class="bottom-nav-item {{ request()->routeIs('search') ? 'active' : '' }}"
        aria-label="Search"
    >
        <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <span class="bottom-nav-label">Search</span>
    </a>

    {{-- Timeline --}}
    <a
        href="{{ route('timeline') }}"
        class="bottom-nav-item {{ request()->routeIs('timeline') ? 'active' : '' }}"
        aria-label="Timeline"
    >
        <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
            <polyline points="17 6 23 6 23 12"/>
        </svg>
        <span class="bottom-nav-label">Timeline</span>
    </a>

    {{-- Inbox --}}
    <a
        href="{{ route('inbox') }}"
        class="bottom-nav-item {{ request()->routeIs('inbox*') ? 'active' : '' }}"
        aria-label="Inbox"
    >
        <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <span class="bottom-nav-label">Inbox</span>
    </a>

    {{-- Profil --}}
    @auth
        <a
            href="{{ auth()->user()->username ? route('profile.show', auth()->user()->username) : route('profile.edit') }}"
            class="bottom-nav-item {{ request()->routeIs('profile.show', 'profile.edit', 'your-space*') ? 'active' : '' }}"
            aria-label="Profil"
        >
            <x-user-avatar :user="auth()->user()" class="bottom-nav-avatar" placeholder-class="bottom-nav-avatar" />
            <span class="bottom-nav-label">Profil</span>
        </a>
    @else
        <a
            href="{{ route('login') }}"
            class="bottom-nav-item"
            aria-label="Masuk"
        >
            <svg class="bottom-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span class="bottom-nav-label">Masuk</span>
        </a>
    @endauth

</nav>
