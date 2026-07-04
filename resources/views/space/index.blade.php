@extends('layouts.movie')

@section('title', 'Your Space — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <div class="space-identity">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="space-avatar">
                    @else
                        <div class="space-avatar space-avatar-placeholder">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="space-name">{{ $user->name }}</h1>
                        @if ($user->username)
                            <p class="space-username">{{ '@' . $user->username }}</p>
                        @endif
                    </div>
                </div>

                <div class="space-stats">
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['total_watched'] }}</span>
                        <span class="space-stat-label">Ditonton</span>
                    </div>
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['total_diary'] }}</span>
                        <span class="space-stat-label">Diary</span>
                    </div>
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['total_reviews'] }}</span>
                        <span class="space-stat-label">Review</span>
                    </div>
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['total_watchlist'] }}</span>
                        <span class="space-stat-label">Watchlist</span>
                    </div>
                </div>
            </div>
        </header>

        <x-space.nav active="index" />
        <x-space.tab-bar active="index" />

        <div class="space-body">
            {{-- Recently Watched --}}
            <section class="space-section">
                <div class="space-section-header">
                    <h2 class="space-section-title">Terakhir Ditonton</h2>
                    <a href="{{ route('your-space.history') }}" class="space-section-link">Lihat semua</a>
                </div>

                @if (empty($recentWatched))
                    <div class="space-empty">
                        Belum ada film yang ditonton. <a href="{{ route('movies.index') }}" class="space-empty-link">Mulai temukan film</a>.
                    </div>
                @else
                    <div class="movie-row">
                        @foreach ($recentWatched as $movie)
                            <x-movie.card :movie="$movie" />
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Watchlist Preview --}}
            <section class="space-section">
                <div class="space-section-header">
                    <h2 class="space-section-title">Watchlist</h2>
                    <a href="{{ route('your-space.watchlist') }}" class="space-section-link">Lihat semua</a>
                </div>

                @if (empty($watchlistMovies))
                    <div class="space-empty">
                        Watchlist kosong.
                    </div>
                @else
                    <div class="movie-row">
                        @foreach ($watchlistMovies as $movie)
                            <x-movie.card :movie="$movie" />
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
