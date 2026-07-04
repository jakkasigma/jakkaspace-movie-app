@extends('layouts.movie')

@section('title', $list->name . ' — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">{{ strtoupper($list->name) }}</h1>
                    @if ($list->description)
                        <p class="space-page-subtitle">{{ $list->description }}</p>
                    @endif
                    <p class="list-show-meta">
                        <span class="list-card-badge {{ $list->is_public ? 'list-badge-public' : 'list-badge-private' }}">
                            {{ $list->is_public ? 'Publik' : 'Privat' }}
                        </span>
                        <span class="list-show-count">{{ count($movies) }} film</span>
                    </p>
                </div>

                @if ($isOwner)
                    <div class="list-show-owner-actions">
                        <a href="{{ route('your-space.lists.edit', $list) }}" class="list-action-link">Edit</a>
                        <form method="POST" action="{{ route('your-space.lists.destroy', $list) }}" class="list-delete-form">
                            @csrf @method('DELETE')
                            <button
                                type="submit"
                                class="list-action-delete"
                                onclick="return confirm('Hapus list ini?')"
                            >Hapus</button>
                        </form>
                    </div>
                @endif
            </div>
        </header>

        <div class="space-body">
            @if (empty($movies))
                <div class="space-empty">
                    List ini masih kosong.
                    @if ($isOwner)
                        Tambah film dari <a href="{{ route('movies.discover') }}" class="space-empty-link">halaman Discover</a> atau detail film.
                    @endif
                </div>
            @else
                <div class="movie-grid">
                    @foreach ($movies as $movie)
                        <div class="movie-grid-item">
                            <x-movie.card :movie="$movie" />
                            @if ($isOwner)
                                <form method="POST" action="{{ route('lists.movies.destroy', [$list, $movie['id']]) }}" class="list-movie-remove-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="list-movie-remove-btn" title="Hapus dari list">✕</button>
                                </form>
                            @endif
                        </div>
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
