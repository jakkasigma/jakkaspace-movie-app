@extends('layouts.movie')

@section('title', 'History — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <h1 class="space-page-title">HISTORY</h1>
                <p class="space-page-subtitle">Riwayat semua film yang kamu tandai.</p>
            </div>
        </header>

        <x-space.nav active="history" />
        <x-space.tab-bar active="history" />

        <div class="space-filters">
            <a href="{{ route('your-space.history') }}"
               class="space-filter-btn {{ $activeStatus === null ? 'active' : '' }}">Semua</a>
            <a href="{{ route('your-space.history', ['status' => 'watched']) }}"
               class="space-filter-btn {{ $activeStatus === 'watched' ? 'active' : '' }}">Watched</a>
            <a href="{{ route('your-space.history', ['status' => 'watching']) }}"
               class="space-filter-btn {{ $activeStatus === 'watching' ? 'active' : '' }}">Watching</a>
            <a href="{{ route('your-space.history', ['status' => 'dropped']) }}"
               class="space-filter-btn {{ $activeStatus === 'dropped' ? 'active' : '' }}">Dropped</a>
        </div>

        <div class="space-body">
            @if ($entries->isEmpty())
                <div class="space-empty">Belum ada riwayat.</div>
            @else
                <div class="history-list">
                    @foreach ($entries as $entry)
                        <div class="history-item">
                            <a href="{{ route('movies.show', $entry->tmdb_id) }}" class="history-link">
                                {{ $entry->movie_title }}
                                @if ($entry->movie_release_year)
                                    <span class="history-year">{{ $entry->movie_release_year }}</span>
                                @endif
                            </a>
                            <span class="history-status status-{{ $entry->status }}">{{ $entry->status }}</span>
                            <span class="history-date">{{ $entry->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>

                @if ($entries->hasPages())
                    <div class="space-pagination">
                        {{ $entries->links() }}
                    </div>
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
