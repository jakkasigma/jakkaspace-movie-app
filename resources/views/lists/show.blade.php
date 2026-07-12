@extends('layouts.movie')

@section('title', $list->name . ' — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        @if ($list->user)
            <a href="{{ route('profile.show', $list->user->username) }}" class="profile-back-link">← Kembali ke profil {{ $list->user->name }}</a>
        @endif

        @if ($list->cover_photo)
            <div class="list-cover-wrap" style="margin-bottom:16px;border-radius:12px;overflow:hidden;max-height:240px;">
                <img src="{{ asset('storage/'.$list->cover_photo) }}" alt="{{ $list->name }} cover" style="width:100%;height:auto;max-height:240px;object-fit:cover;display:block;">
            </div>
        @endif

        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">{{ strtoupper($list->name) }}</h1>
                    @if ($list->description)
                        <p class="space-page-subtitle">{{ $list->description }}</p>
                    @endif
                    <div class="list-show-meta" style="flex-wrap: wrap; gap: 8px;">
                        <span class="list-card-badge {{ $list->is_public ? 'list-badge-public' : 'list-badge-private' }}">
                            {{ $list->is_public ? 'Publik' : 'Privat' }}
                        </span>
                        <span class="list-show-count">{{ count($movies) }} film</span>
                        <span class="list-show-count">👥 {{ $list->approvedMembers()->count() }} anggota</span>
                        @if ($list->code)
                            <span style="font-size: 0.7rem; font-family: 'Courier New', monospace; color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); border-radius: 4px; padding: 2px 8px; letter-spacing: 1px; white-space: nowrap;">Kode: {{ $list->code }}</span>
                        @endif
                    </div>
                </div>

                <div class="list-show-owner-actions" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    @auth
                        @if ($isOwner)
                            <a href="{{ route('your-space.lists.edit', $list) }}" class="list-action-link">Edit</a>
                            <a href="{{ route('lists.members.manage', $list) }}" class="list-action-link">Anggota</a>
                            <form method="POST" action="{{ route('your-space.lists.destroy', $list) }}" class="list-delete-form" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="list-action-delete" onclick="return confirm('Hapus list ini?')">Hapus</button>
                            </form>
                        @elseif ($isMember)
                            <form method="POST" action="{{ route('lists.members.leave', $list) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="list-action-link" style="color: #ef4444;" onclick="return confirm('Yakin keluar dari list ini?')">Keluar</button>
                            </form>
                        @elseif ($isPending)
                            <span class="list-card-badge">Menunggu Persetujuan</span>
                        @else
                            <form method="POST" action="{{ route('lists.members.join', $list) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="list-action-link" style="color: #22c55e;">+ Gabung</button>
                            </form>
                        @endif
                        @if ($isOwner || $isMember)
                            <button onclick="copyInviteCode()" class="list-action-link" style="display: flex; align-items: center; gap: 4px;">📋 Salin Kode</button>
                            <input type="text" id="invite-code" value="{{ $list->code }}" readonly style="position:absolute;left:-9999px;">
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        {{-- Tabs --}}
        <nav class="list-tab-bar">
            <a href="{{ route('lists.show', ['list' => $list, 'tab' => 'movies']) }}" class="list-tab {{ $tab === 'movies' ? 'active' : '' }}">Film</a>
            <a href="{{ route('lists.show', ['list' => $list, 'tab' => 'members']) }}" class="list-tab {{ $tab === 'members' ? 'active' : '' }}">Anggota</a>
            @if ($isOwner || $isMember)
                <a href="{{ route('lists.chat.show', $list) }}" class="list-tab">Chat</a>
            @endif
        </nav>

        {{-- Tab Content --}}
        <div class="space-body">
            @if ($tab === 'movies')
                @if (! $canViewMovies)
                    <div class="space-empty">
                        Gabung ke list ini untuk melihat film-filmnya.
                    </div>
                @elseif (empty($movies))
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
                                @if ($isOwner || $userRole === 'admin')
                                    <form method="POST" action="{{ route('lists.movies.destroy', [$list, $movie['id']]) }}" class="list-movie-remove-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="list-movie-remove-btn" title="Hapus dari list">✕</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

            @elseif ($tab === 'chat')
                @if ($isOwner || $isMember)
                    @include('lists.chat')
                @else
                    <div class="space-empty">Gabung ke list untuk mengobrol dengan anggota.</div>
                @endif

            @elseif ($tab === 'members')
                @include('lists.members')
            @endif
        </div>
    </main>

    @if ($isOwner || $isMember)
    <script>
        function copyInviteCode() {
            const input = document.getElementById('invite-code');
            input.style.position = 'static';
            input.select();
            document.execCommand('copy');
            input.style.position = 'absolute';
            alert('Kode undangan disalin!');
        }
    </script>
    @endif
@endsection
