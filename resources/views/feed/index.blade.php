@extends('layouts.movie')

@section('title', 'Feed — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <h1 class="space-page-title">FEED</h1>
                <p class="space-page-subtitle">Aktivitas terbaru dari orang yang kamu ikuti.</p>
            </div>
        </header>

        <div class="space-body">
            {{-- Trending among following --}}
            @if (! empty($trendingFollowing))
                <section class="feed-trending-section">
                    <div class="space-section-header">
                        <h2 class="space-section-title">Lagi Ditonton Following</h2>
                        <span class="feed-trending-badge">30 hari terakhir</span>
                    </div>
                    <div class="movie-row">
                        @foreach ($trendingFollowing as $movie)
                            <x-movie.card :movie="$movie" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($feed->isEmpty())
                <div class="space-empty">
                    Feed kosong. <a href="{{ route('movies.discover') }}" class="space-empty-link">Temukan film</a> atau follow pengguna lain untuk mulai.
                </div>
            @else
                <div class="feed-list">
                    @foreach ($feed as $item)
                        <article class="feed-item">
                            {{-- User avatar --}}
                            <div class="feed-item-avatar">
                                @if ($item['user']->avatar_url)
                                    <img src="{{ $item['user']->avatar_url }}" alt="{{ $item['user']->name }}" class="feed-avatar">
                                @else
                                    <div class="feed-avatar feed-avatar-placeholder">
                                        {{ strtoupper(substr($item['user']->name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Activity text --}}
                            <div class="feed-item-body">
                                <p class="feed-item-text">
                                    @if ($item['user']->username)
                                        <a href="{{ route('profile.show', $item['user']->username) }}" class="feed-user-link">{{ $item['user']->name }}</a>
                                    @else
                                        <strong class="feed-user-link">{{ $item['user']->name }}</strong>
                                    @endif

                                    @php $movieTitle = $item['title'] ?? 'sebuah film'; @endphp

                                    @if ($item['type'] === 'diary')
                                        menonton
                                        <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="feed-movie-link">{{ $movieTitle }}</a>
                                        @if ($item['extra'])
                                            <span class="feed-mood">· {{ $item['extra'] }}</span>
                                        @endif
                                    @elseif ($item['type'] === 'review')
                                        menulis review untuk
                                        <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="feed-movie-link">{{ $movieTitle }}</a>
                                        @if ($item['extra'])
                                            <span class="feed-rating">· ★ {{ $item['extra'] }}/10</span>
                                        @endif
                                    @elseif ($item['type'] === 'watchlist')
                                        menambahkan
                                        <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="feed-movie-link">{{ $movieTitle }}</a>
                                        ke watchlist
                                    @elseif ($item['type'] === 'favorite')
                                        menandai
                                        <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="feed-movie-link">{{ $movieTitle }}</a>
                                        sebagai favorit
                                    @elseif ($item['type'] === 'list')
                                        membuat list
                                        <a href="{{ route('lists.show', $item['extra']) }}" class="feed-movie-link">{{ $item['title'] }}</a>
                                    @endif
                                </p>
                                <span class="feed-item-time">{{ $item['created_at']->diffForHumans() }}</span>
                            </div>

                            {{-- Poster mini (kalau ada) --}}
                            @if (! empty($item['poster_url']) && $item['type'] !== 'list')
                                <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="feed-item-poster-link" tabindex="-1" aria-hidden="true">
                                    <img src="{{ $item['poster_url'] }}" alt="{{ $item['title'] }}" class="feed-item-poster" loading="lazy">
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
