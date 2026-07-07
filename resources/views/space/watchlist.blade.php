@extends('layouts.movie')

@section('title', 'Watchlist — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <a href="{{ route('your-space') }}" class="profile-back-link">← Your Space</a>
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">WATCHLIST</h1>
                    <p class="space-page-subtitle">Film yang ingin kamu tonton.</p>
                </div>
                <div class="space-header-stats">
                    <span class="space-header-stat">{{ $watchlistInfo['count'] }} film</span>
                    @if ($watchlistInfo['avg_rating'])
                        <span class="space-header-stat-sep">·</span>
                        <span class="space-header-stat">Rata-rata {{ $watchlistInfo['avg_rating'] }}/10</span>
                    @endif
                </div>
            </div>
        </header>

        <x-space.nav active="watchlist" />
        <x-space.tab-bar active="watchlist" />

        <div class="space-body">
            @if (empty($movies))
                <x-space.empty icon="film" message="Watchlist kosong." :link="route('movies.discover')" linkText="Temukan film dan tambahkan ke watchlist" />
            @else
                <div class="movie-grid">
                    @foreach ($movies as $movie)
                        <x-movie.card :movie="$movie" :rank="$loop->iteration" />
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
