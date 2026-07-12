@extends('layouts.movie')

@section('title', 'Timeline — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="timeline-page">

        {{-- Page header + tabs --}}
        <header class="timeline-header">
            <div class="timeline-header-inner">
                <div class="timeline-title-row">
                    <h1 class="timeline-title">TIMELINE</h1>
                    <p class="timeline-subtitle">Apa yang sedang terjadi di dunia film.</p>
                </div>

                <nav class="timeline-tabs" aria-label="Tab timeline">
                    <a href="{{ route('timeline', ['tab' => 'all']) }}"
                       class="timeline-tab {{ $tab === 'all' ? 'active' : '' }}"
                       aria-current="{{ $tab === 'all' ? 'page' : 'false' }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        Semua
                    </a>
                    <a href="{{ route('timeline', ['tab' => 'trending']) }}"
                       class="timeline-tab {{ $tab === 'trending' ? 'active' : '' }}"
                       aria-current="{{ $tab === 'trending' ? 'page' : 'false' }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                            <polyline points="17 6 23 6 23 12"/>
                        </svg>
                        Trending
                    </a>
                    <a href="{{ route('timeline', ['tab' => 'following']) }}"
                       class="timeline-tab {{ $tab === 'following' ? 'active' : '' }}"
                       aria-current="{{ $tab === 'following' ? 'page' : 'false' }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Following
                    </a>
                </nav>
            </div>
        </header>

        {{-- Tab content --}}
        <div class="timeline-body">

            {{-- ===== TAB: SEMUA ===== --}}
            @if ($tab === 'all')

                {{-- Trending TMDB --}}
                @if (! empty($trending_movies))
                    <section class="timeline-section">
                        <div class="timeline-section-header">
                            <h2 class="timeline-section-title">🔥 Trending TMDB Minggu Ini</h2>
                            <span class="timeline-section-badge">TMDB</span>
                        </div>
                        <div class="movie-row">
                            @foreach (array_slice($trending_movies, 0, 10) as $movie)
                                <x-movie.card :movie="$movie" />
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Review terpopuler --}}
                @if ($popular_reviews->isNotEmpty())
                    <section class="timeline-section">
                        <div class="timeline-section-header">
                            <h2 class="timeline-section-title">⭐ Review Terpopuler 7 Hari Ini</h2>
                        </div>
                        <div class="timeline-review-list">
                            @foreach ($popular_reviews as $review)
                                <x-timeline.review-card :review="$review" />
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Film paling banyak di-watchlist --}}
                @if (! empty($most_watchlisted))
                    <section class="timeline-section">
                        <div class="timeline-section-header">
                            <h2 class="timeline-section-title">📋 Paling Banyak Masuk Watchlist</h2>
                            <span class="timeline-section-badge">7 hari terakhir</span>
                        </div>
                        <div class="movie-row">
                            @foreach ($most_watchlisted as $item)
                                @if ($item['movie'])
                                    <x-movie.card :movie="$item['movie']" :badge="$item['count'] . 'x'" />
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Film paling banyak di-review --}}
                @if (! empty($most_reviewed))
                    <section class="timeline-section">
                        <div class="timeline-section-header">
                            <h2 class="timeline-section-title">✍️ Paling Banyak Diulas</h2>
                            <span class="timeline-section-badge">7 hari terakhir</span>
                        </div>
                        <div class="movie-row">
                            @foreach ($most_reviewed as $item)
                                @if ($item['movie'])
                                    <x-movie.card :movie="$item['movie']" :badge="$item['count'] . ' review'" />
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (empty($trending_movies) && $popular_reviews->isEmpty() && empty($most_watchlisted) && empty($most_reviewed))
                    <div class="timeline-empty">
                        Belum ada data timeline minggu ini.
                    </div>
                @endif

            {{-- ===== TAB: TRENDING ===== --}}
            @elseif ($tab === 'trending')

                {{-- Trending TMDB --}}
                @if (! empty($trending_movies))
                    <section class="timeline-section">
                        <div class="timeline-section-header">
                            <h2 class="timeline-section-title">🔥 Film Trending TMDB</h2>
                            <span class="timeline-section-badge">Minggu ini</span>
                        </div>
                        <div class="movie-row">
                            @foreach (array_slice($trending_movies, 0, 10) as $movie)
                                <x-movie.card :movie="$movie" />
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Review likes terbanyak --}}
                @if ($top_liked_reviews->isNotEmpty())
                    <section class="timeline-section">
                        <div class="timeline-section-header">
                            <h2 class="timeline-section-title">👍 Review dengan Likes Terbanyak</h2>
                            <span class="timeline-section-badge">7 hari terakhir</span>
                        </div>
                        <div class="timeline-review-list">
                            @foreach ($top_liked_reviews as $review)
                                <x-timeline.review-card :review="$review" />
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Film paling banyak di-diary --}}
                @if (! empty($most_diary))
                    <section class="timeline-section">
                        <div class="timeline-section-header">
                            <h2 class="timeline-section-title">📓 Paling Banyak Dicatat di Diary</h2>
                            <span class="timeline-section-badge">7 hari terakhir</span>
                        </div>
                        <div class="movie-row">
                            @foreach ($most_diary as $item)
                                @if ($item['movie'])
                                    <x-movie.card :movie="$item['movie']" :badge="$item['count'] . 'x'" />
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (empty($trending_movies) && $top_liked_reviews->isEmpty() && empty($most_diary))
                    <div class="timeline-empty">
                        Belum ada data trending minggu ini.
                    </div>
                @endif

            {{-- ===== TAB: FOLLOWING ===== --}}
            @elseif ($tab === 'following')

                @guest
                    <div class="timeline-login-prompt">
                        <p class="timeline-login-title">Masuk untuk lihat aktivitas following</p>
                        <p class="timeline-login-hint">Follow pengguna lain untuk melihat apa yang mereka tonton, tulis, dan simpan.</p>
                        <a href="{{ route('login') }}" class="timeline-login-btn">Masuk</a>
                    </div>
                @else

                    {{-- Trending among following --}}
                    @if (! empty($trending_among_following))
                        <section class="timeline-section">
                            <div class="timeline-section-header">
                                <h2 class="timeline-section-title">🔥 Lagi Ditonton Following</h2>
                                <span class="timeline-section-badge">30 hari terakhir</span>
                            </div>
                            <div class="movie-row">
                                @foreach ($trending_among_following as $movie)
                                    <x-movie.card :movie="$movie" />
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Feed aktivitas --}}
                    @if ($feed->isEmpty())
                        <div class="timeline-empty">
                            Belum ada aktivitas.
                            <a href="{{ route('search', ['tab' => 'users']) }}" class="timeline-empty-link">Temukan pengguna</a>
                            untuk di-follow.
                        </div>
                    @else
                        <section class="timeline-section timeline-section--feed">
                            <div class="timeline-section-header">
                                <h2 class="timeline-section-title">Aktivitas Terbaru</h2>
                            </div>
                            <div class="timeline-feed-list">
                                @foreach ($feed as $item)
                                    <x-timeline.feed-item :item="$item" />
                                @endforeach
                            </div>
                        </section>
                    @endif

                @endguest
            @endif

        </div>
    </main>

    @endsection
