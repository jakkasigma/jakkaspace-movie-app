@extends('layouts.movie')

@section('title', $query !== '' ? "Pencarian: {$query} — Jakka Space" : 'Cari — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="search-page">

        {{-- Search header --}}
        <header class="search-header">
            <div class="search-header-inner">
                <form method="GET" action="{{ route('search') }}" class="search-form" role="search">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="search-input-wrap">
                        <svg class="search-input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input
                            type="search"
                            name="q"
                            value="{{ $query }}"
                            placeholder="Cari film, pengguna, atau list..."
                            class="search-input"
                            autofocus
                            autocomplete="off"
                            aria-label="Kata kunci pencarian"
                        >
                        @if ($query !== '')
                            <a href="{{ route('search', ['tab' => $tab]) }}" class="search-clear-btn" aria-label="Hapus pencarian">✕</a>
                        @endif
                    </div>
                </form>

                {{-- Tab navigation --}}
                <nav class="search-tabs" aria-label="Tab pencarian">
                    <a href="{{ route('search', array_filter(['q' => $query, 'tab' => 'films'])) }}"
                       class="search-tab {{ $tab === 'films' ? 'active' : '' }}"
                       aria-current="{{ $tab === 'films' ? 'page' : 'false' }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/>
                            <line x1="7" y1="2" x2="7" y2="22"/>
                            <line x1="17" y1="2" x2="17" y2="22"/>
                            <line x1="2" y1="12" x2="22" y2="12"/>
                            <line x1="2" y1="7" x2="7" y2="7"/>
                            <line x1="2" y1="17" x2="7" y2="17"/>
                            <line x1="17" y1="17" x2="22" y2="17"/>
                            <line x1="17" y1="7" x2="22" y2="7"/>
                        </svg>
                        Film
                    </a>
                    <a href="{{ route('search', array_filter(['q' => $query, 'tab' => 'users'])) }}"
                       class="search-tab {{ $tab === 'users' ? 'active' : '' }}"
                       aria-current="{{ $tab === 'users' ? 'page' : 'false' }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Pengguna
                    </a>
                    <a href="{{ route('search', array_filter(['q' => $query, 'tab' => 'lists'])) }}"
                       class="search-tab {{ $tab === 'lists' ? 'active' : '' }}"
                       aria-current="{{ $tab === 'lists' ? 'page' : 'false' }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="8" y1="6" x2="21" y2="6"/>
                            <line x1="8" y1="12" x2="21" y2="12"/>
                            <line x1="8" y1="18" x2="21" y2="18"/>
                            <line x1="3" y1="6" x2="3.01" y2="6"/>
                            <line x1="3" y1="12" x2="3.01" y2="12"/>
                            <line x1="3" y1="18" x2="3.01" y2="18"/>
                        </svg>
                        List
                    </a>
                </nav>
            </div>
        </header>

        {{-- Tab content --}}
        <div class="search-body">

            {{-- Empty / default state --}}
            @if ($query === '')
                <div class="search-empty-state">
                    <svg class="search-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <p class="search-empty-title">Cari apa hari ini?</p>
                    <p class="search-empty-hint">
                        @if ($tab === 'films') Ketik judul film untuk mencarinya di TMDB.
                        @elseif ($tab === 'users') Ketik nama atau username untuk menemukan pengguna.
                        @else Ketik nama list untuk mencari list publik.
                        @endif
                    </p>
                </div>

            {{-- Film tab --}}
            @elseif ($tab === 'films')
                @if (empty($movies))
                    <div class="search-no-results">
                        <p>Film dengan kata kunci <strong>"{{ $query }}"</strong> tidak ditemukan.</p>
                    </div>
                @else
                    <p class="search-result-count">
                        Menampilkan hasil untuk <strong>"{{ $query }}"</strong>
                    </p>
                    <div class="search-movie-grid">
                        @foreach ($movies as $movie)
                            <x-movie.card :movie="$movie" />
                        @endforeach
                    </div>
                @endif

            {{-- User tab --}}
            @elseif ($tab === 'users')
                @if ($users === null || $users->isEmpty())
                    <div class="search-no-results">
                        <p>Pengguna dengan kata kunci <strong>"{{ $query }}"</strong> tidak ditemukan.</p>
                    </div>
                @else
                    <p class="search-result-count">
                        {{ $users->total() }} pengguna ditemukan untuk <strong>"{{ $query }}"</strong>
                    </p>
                    <div class="search-user-list">
                        @foreach ($users as $user)
                            <article class="search-user-card">
                                <a href="{{ route('profile.show', $user->username ?? $user->id) }}" class="search-user-avatar-link" tabindex="-1" aria-hidden="true">
                                    @if ($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="search-user-avatar">
                                    @else
                                        <div class="search-user-avatar search-user-avatar-placeholder" aria-hidden="true">
                                            {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
                                        </div>
                                    @endif
                                </a>
                                <div class="search-user-info">
                                    <a href="{{ route('profile.show', $user->username ?? $user->id) }}" class="search-user-name">
                                        {{ $user->name }}
                                    </a>
                                    @if ($user->username)
                                        <span class="search-user-handle">@{{ $user->username }}</span>
                                    @endif
                                    <span class="search-user-followers">{{ $user->followers_count }} followers</span>
                                </div>
                                <div class="search-user-action">
                                    @auth
                                        @if (auth()->id() !== $user->id)
                                            @if (auth()->user()->following->contains($user->id))
                                                <form method="POST" action="{{ route('users.unfollow', $user) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="search-follow-btn search-follow-btn--following">Following</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('users.follow', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="search-follow-btn">Follow</button>
                                                </form>
                                            @endif
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="search-follow-btn">Follow</a>
                                    @endauth
                                </div>
                            </article>
                        @endforeach
                    </div>
                    {{ $users->links() }}
                @endif

            {{-- List tab --}}
            @elseif ($tab === 'lists')
                @if ($lists === null || $lists->isEmpty())
                    <div class="search-no-results">
                        <p>List dengan kata kunci <strong>"{{ $query }}"</strong> tidak ditemukan.</p>
                    </div>
                @else
                    <p class="search-result-count">
                        {{ $lists->total() }} list ditemukan untuk <strong>"{{ $query }}"</strong>
                    </p>
                    <div class="search-list-grid">
                        @foreach ($lists as $list)
                            <article class="search-list-card">
                                <div class="search-list-card-body">
                                    <a href="{{ route('lists.show', $list) }}" class="search-list-name">{{ $list->name }}</a>
                                    @if ($list->description)
                                        <p class="search-list-desc">{{ $list->description }}</p>
                                    @endif
                                    <div class="search-list-meta">
                                        @if ($list->user)
                                            <a href="{{ route('profile.show', $list->user->username ?? $list->user->id) }}" class="search-list-owner">
                                                @if ($list->user->avatar_url)
                                                    <img src="{{ $list->user->avatar_url }}" alt="{{ $list->user->name }}" class="search-list-owner-avatar">
                                                @else
                                                    <span class="search-list-owner-avatar search-list-owner-avatar-placeholder">
                                                        {{ strtoupper(substr($list->user->name ?? '?', 0, 1)) }}
                                                    </span>
                                                @endif
                                                {{ $list->user->name }}
                                            </a>
                                        @endif
                                        <span class="search-list-count">{{ $list->list_movies_count }} film</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    {{ $lists->links() }}
                @endif
            @endif

        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
