@props([])

<nav id="navbar">
    {{-- Logo --}}
    <a href="{{ route('movies.index', [], false) }}" class="nav-logo" aria-label="Jakka Space">
        <span class="nav-jakka">JAKKA</span>
        <span class="nav-space-wrap">
            <span class="nav-letter" style="color:#40E0D0;">S</span>
            <span class="nav-letter" style="color:#FF0000;">P</span>
            <span class="nav-letter" style="color:#FF69B4;">A</span>
            <span class="nav-letter" style="color:#00FF00;">C</span>
            <span class="nav-letter" style="color:#8A2BE2;">E</span>
        </span>
    </a>

    {{--
        DESKTOP CENTER — nav links saja (search sudah punya halaman /search)
        Di mobile disembunyikan karena sudah ada di bottom nav
    --}}
    <div class="nav-center nav-desktop-only">
        <ul class="nav-links">
            <li>
                <a
                    href="{{ route('movies.index', [], false) }}"
                    class="{{ request()->routeIs('movies.index') ? 'active' : '' }}"
                >HOME</a>
            </li>
            <li>
                <a
                    href="{{ route('timeline', [], false) }}"
                    class="{{ request()->routeIs('timeline') ? 'active' : '' }}"
                >TIMELINE</a>
            </li>
            <li>
                <a
                    href="{{ route('search', [], false) }}"
                    class="{{ request()->routeIs('search') ? 'active' : '' }}"
                >SEARCH</a>
            </li>
            <li class="nav-li-inbox">
                <a
                    href="{{ route('inbox', [], false) }}"
                    class="{{ request()->routeIs('inbox*') ? 'active' : '' }}"
                >INBOX
                    @auth
                        @if ($inboxUnreadCount > 0)
                            <span class="nav-inbox-badge">{{ $inboxUnreadCount > 9 ? '9+' : $inboxUnreadCount }}</span>
                        @endif
                    @endauth
                </a>
            </li>
        </ul>
    </div>

    {{--
        RIGHT SECTION — notif bell + avatar (desktop) / notif bell (mobile)
        nav-right selalu tampil. nav-user disembunyikan di mobile via CSS.
    --}}
    <div class="nav-right">
        {{-- Notification bell — satu elemen, tampil di semua ukuran layar --}}
        @auth
            <a href="{{ route('notifications') }}" class="nav-notif-btn" aria-label="Notifikasi">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                @if ($unreadCount > 0)
                    <span class="nav-notif-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </a>
        @endauth

        {{-- Avatar dropdown (desktop only, hidden on mobile via CSS) --}}
        <div class="nav-user">
            @auth
                <div class="nav-user-menu" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button
                        type="button"
                        class="nav-user-trigger"
                        @click="open = !open"
                        :aria-expanded="open"
                        aria-haspopup="true"
                        aria-label="Menu profil"
                    >
                        <x-user-avatar :user="auth()->user()" class="nav-user-avatar" placeholder-class="nav-user-initial" />
                    </button>

                    <div
                        class="nav-dropdown"
                        x-show="open"
                        x-transition:enter="nav-dropdown-enter"
                        x-transition:enter-start="nav-dropdown-enter-start"
                        x-transition:enter-end="nav-dropdown-enter-end"
                        x-transition:leave="nav-dropdown-leave"
                        x-transition:leave-start="nav-dropdown-leave-start"
                        x-transition:leave-end="nav-dropdown-leave-end"
                        @click.outside="open = false"
                        x-cloak
                    >
                        <div class="nav-dropdown-header">
                            <p class="nav-dropdown-name">{{ auth()->user()->name }}</p>
                            @if (auth()->user()->username)
                                <span class="nav-dropdown-username">{{ '@' . auth()->user()->username }}</span>
                            @endif
                        </div>

                        <div class="nav-dropdown-divider"></div>

                        <nav class="nav-dropdown-links">
                            @if (auth()->user()->username)
                                <a href="{{ route('profile.show', auth()->user()->username) }}" class="nav-dropdown-item nav-dropdown-item-profile">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                    Profil Saya
                                </a>
                            @else
                                <a href="{{ route('profile.edit') }}" class="nav-dropdown-item nav-dropdown-item-profile">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                    Set Username →
                                </a>
                            @endif
                            <a href="{{ route('your-space') }}" class="nav-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                Your Space
                            </a>
                            <a href="{{ route('plus') }}" class="nav-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                Plus
                                @if (auth()->user()->isPlus())
                                    <span style="color:#4caf50;font-size:0.7rem;margin-left:4px;">✓ Aktif</span>
                                @endif
                            </a>
                            <a href="{{ route('your-space.diary') }}" class="nav-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                Diary
                            </a>
                            <a href="{{ route('your-space.history') }}" class="nav-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                History
                            </a>
                            <a href="{{ route('your-space.watchlist') }}" class="nav-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                Watchlist
                            </a>
                            <a href="{{ route('your-space.favorites') }}" class="nav-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                Favorit
                            </a>
                            <a href="{{ route('your-space.lists') }}" class="nav-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                Lists
                            </a>
                            <a href="{{ route('timeline') }}" class="nav-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                                Timeline
                            </a>
                            @if (auth()->user()->isAdmin())
                                <div class="nav-dropdown-divider"></div>
                                <a href="{{ route('admin.dashboard') }}" class="nav-dropdown-item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                    Admin Panel
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="nav-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                Pengaturan
                            </a>
                        </nav>

                        <div class="nav-dropdown-divider"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-dropdown-item nav-dropdown-logout">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="nav-login-btn">Masuk</a>
            @endauth
        </div>
    </div>

    @php
        $showHamburger = request()->routeIs('profile.show', 'profile.edit', 'your-space*');
    @endphp

    @if ($showHamburger)
        <button class="hamburger" type="button" aria-label="Menu personal" data-menu-button>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>

        <div class="nav-mobile-panel" data-menu-panel>
            @auth
                <div class="nav-mobile-identity">
                    @if (auth()->user()->username)
                        <a href="{{ route('profile.show', auth()->user()->username) }}" class="nav-mobile-identity-link" data-menu-link>
                    @else
                        <a href="{{ route('profile.edit') }}" class="nav-mobile-identity-link" data-menu-link>
                    @endif
                        <div class="nav-mobile-identity-avatar">
                            <x-user-avatar :user="auth()->user()" class="nav-mobile-avatar" placeholder-class="nav-mobile-initial" />
                        </div>
                        <div class="nav-mobile-identity-info">
                            <p class="nav-mobile-name">{{ auth()->user()->name }}</p>
                            @if (auth()->user()->username)
                                <p class="nav-mobile-username">{{ '@' . auth()->user()->username }}</p>
                            @else
                                <p class="nav-mobile-username">Set username →</p>
                            @endif
                        </div>
                        <svg class="nav-mobile-profile-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </a>
                </div>

                <div class="nav-mobile-divider"></div>

                <div class="nav-mobile-section">
                    <p class="nav-mobile-section-label">Personal</p>
                    <a href="{{ route('your-space') }}" class="nav-mobile-link {{ request()->routeIs('your-space') ? 'active' : '' }}" data-menu-link>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Your Space
                    </a>
                    <a href="{{ route('your-space.analytics') }}" class="nav-mobile-link {{ request()->routeIs('your-space.analytics') ? 'active' : '' }}" data-menu-link>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        Statistik
                    </a>
                    <a href="{{ route('your-space.diary') }}" class="nav-mobile-link {{ request()->routeIs('your-space.diary') ? 'active' : '' }}" data-menu-link>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Diary
                    </a>
                    <a href="{{ route('your-space.history') }}" class="nav-mobile-link {{ request()->routeIs('your-space.history') ? 'active' : '' }}" data-menu-link>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        History
                    </a>
                    <a href="{{ route('your-space.watchlist') }}" class="nav-mobile-link {{ request()->routeIs('your-space.watchlist') ? 'active' : '' }}" data-menu-link>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                        Watchlist
                    </a>
                    <a href="{{ route('your-space.favorites') }}" class="nav-mobile-link {{ request()->routeIs('your-space.favorites') ? 'active' : '' }}" data-menu-link>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        Favorit
                    </a>
                    <a href="{{ route('your-space.lists') }}" class="nav-mobile-link {{ request()->routeIs('your-space.lists*') ? 'active' : '' }}" data-menu-link>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        Lists
                    </a>
                </div>

                <div class="nav-mobile-divider"></div>

                <div class="nav-mobile-section">
                    <p class="nav-mobile-section-label">Plus</p>
                    <a href="{{ route('plus') }}" class="nav-mobile-link {{ request()->routeIs('plus') ? 'active' : '' }}" data-menu-link>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Plus
                        @if (auth()->user()->isPlus())
                            <span style="color:#4caf50;font-size:0.7rem;margin-left:4px;">✓ Aktif</span>
                        @endif
                    </a>
                </div>

                @if (auth()->user()->isAdmin())
                    <div class="nav-mobile-section">
                        <p class="nav-mobile-section-label">Admin</p>
                        <a href="{{ route('admin.dashboard') }}" class="nav-mobile-link" data-menu-link>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            Admin Panel
                        </a>
                    </div>
                @endif

                <div class="nav-mobile-divider"></div>

                <div class="nav-mobile-section">
                    <p class="nav-mobile-section-label">Akun</p>
                    <a href="{{ route('profile.edit') }}" class="nav-mobile-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" data-menu-link>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        Pengaturan
                    </a>
                </div>

                <div class="nav-mobile-logout-wrap">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-mobile-logout-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>

            @else
                <div class="nav-mobile-guest">
                    <p class="nav-mobile-guest-title">Jakka Space</p>
                    <p class="nav-mobile-guest-hint">Masuk untuk mulai catat film favoritmu.</p>
                    <a href="{{ route('login') }}" class="nav-mobile-guest-login" data-menu-link>Masuk</a>
                    <a href="{{ route('register') }}" class="nav-mobile-guest-register" data-menu-link>Daftar</a>
                </div>
            @endauth
        </div>
    @endif
</nav>
