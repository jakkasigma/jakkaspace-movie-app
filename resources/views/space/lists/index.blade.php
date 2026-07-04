@extends('layouts.movie')

@section('title', 'Lists — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">LISTS</h1>
                    <p class="space-page-subtitle">Koleksi film personalmu.</p>
                </div>
                <a href="{{ route('your-space.lists.create') }}" class="btn-create-list">+ Buat List</a>
            </div>
        </header>

        <x-space.nav active="lists" />
        <x-space.tab-bar active="lists" />

        <div class="space-body">
            @if ($lists->isEmpty())
                <div class="space-empty">
                    Belum ada list. <a href="{{ route('your-space.lists.create') }}" class="space-empty-link">Buat list pertamamu</a>.
                </div>
            @else
                <div class="lists-grid">
                    @foreach ($lists as $list)
                        <article class="list-card">
                            <div class="list-card-header">
                                <div>
                                    <a href="{{ route('lists.show', $list) }}" class="list-card-name">{{ $list->name }}</a>
                                    <span class="list-card-badge {{ $list->is_public ? 'list-badge-public' : 'list-badge-private' }}">
                                        {{ $list->is_public ? 'Publik' : 'Privat' }}
                                    </span>
                                </div>
                                <span class="list-card-count">{{ $list->list_movies_count }} film</span>
                            </div>

                            @if ($list->description)
                                <p class="list-card-desc">{{ $list->description }}</p>
                            @endif

                            <div class="list-card-actions">
                                <a href="{{ route('lists.show', $list) }}" class="list-action-link">Lihat</a>
                                <a href="{{ route('your-space.lists.edit', $list) }}" class="list-action-link">Edit</a>
                                <form method="POST" action="{{ route('your-space.lists.destroy', $list) }}" class="list-delete-form">
                                    @csrf @method('DELETE')
                                    <button
                                        type="submit"
                                        class="list-action-delete"
                                        onclick="return confirm('Hapus list \'{{ addslashes($list->name) }}\'?')"
                                    >Hapus</button>
                                </form>
                            </div>
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
