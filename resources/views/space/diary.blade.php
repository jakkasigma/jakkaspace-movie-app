@extends('layouts.movie')

@section('title', 'Diary — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <h1 class="space-page-title">DIARY</h1>
                <p class="space-page-subtitle">Catatan perjalanan menontonmu.</p>
            </div>
        </header>

        <x-space.nav active="diary" />
        <x-space.tab-bar active="diary" />

        <div class="space-body">
            @if ($entries->isEmpty())
                <div class="space-empty">
                    Belum ada diary. Buka halaman film dan mulai tulis catatanmu.
                </div>
            @else
                <div class="diary-list">
                    @foreach ($entries as $entry)
                        <article class="diary-card">
                            <div class="diary-card-date">
                                <span class="diary-date-day">{{ $entry->watched_at->format('d') }}</span>
                                <span class="diary-date-month">{{ $entry->watched_at->locale('id')->translatedFormat('M Y') }}</span>
                            </div>

                            <div class="diary-card-body">
                                <div class="diary-card-meta">
                                    <a href="{{ route('movies.show', $entry->tmdb_id) }}" class="diary-movie-link">
                                        {{ $entry->movie_title }}
                                        @if ($entry->movie_release_year)
                                            <span class="diary-movie-year">({{ $entry->movie_release_year }})</span>
                                        @endif
                                    </a>

                                    @if ($entry->mood)
                                        <span class="diary-mood">{{ $entry->mood }}</span>
                                    @endif

                                    @if ($entry->is_rewatch)
                                        <span class="diary-rewatch">Rewatch</span>
                                    @endif
                                </div>

                                @if ($entry->notes)
                                    <p class="diary-notes">{{ $entry->notes }}</p>
                                @endif

                                <form method="POST" action="{{ route('diary.destroy', $entry) }}" class="diary-delete-form">
                                    @csrf @method('DELETE')
                                    <button
                                        type="submit"
                                        class="diary-delete-btn"
                                        onclick="return confirm('Hapus diary ini?')"
                                    >Hapus</button>
                                </form>
                            </div>
                        </article>
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
