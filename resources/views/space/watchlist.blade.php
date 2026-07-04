@extends('layouts.movie')

@section('title', 'Watchlist — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <h1 class="space-page-title">WATCHLIST</h1>
                <p class="space-page-subtitle">Film yang ingin kamu tonton.</p>
            </div>
        </header>

        <x-space.nav active="watchlist" />
        <x-space.tab-bar active="watchlist" />

        <div class="space-body">
            @if (empty($movies))
                <div class="space-empty">
                    Watchlist kosong. <a href="{{ route('movies.discover') }}" class="space-empty-link">Temukan film</a> dan tambahkan ke watchlist.
                </div>
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
