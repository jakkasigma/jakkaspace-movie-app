@extends('layouts.movie')

@section('title', 'Lists — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <a href="{{ route('your-space') }}" class="profile-back-link">← Your Space</a>
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">LISTS</h1>
                    <p class="space-page-subtitle">Koleksi film dan grup bersama teman.</p>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <a href="{{ route('your-space.lists.create') }}" class="btn-create-list">+ Buat List</a>
                </div>
            </div>
        </header>

        <x-space.nav active="lists" />
        <x-space.tab-bar active="lists" />

        {{-- Join by Code --}}
        <div style="margin-bottom: 24px; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 8px; display: flex; gap: 8px; align-items: center;">
            <form method="POST" action="{{ route('lists.join-by-code') }}" style="display: flex; gap: 8px; flex: 1;">
                @csrf
                <input type="text" name="code" placeholder="Masukkan kode undangan..." required
                    style="flex: 1; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 9px 14px; color: #fff; font-size: 0.85rem; outline: none; max-width: 300px;">
                <button type="submit" style="background: rgba(255,255,255,0.08); border: none; border-radius: 6px; padding: 9px 18px; color: #fff; font-size: 0.82rem; font-weight: 600; cursor: pointer;">Gabung</button>
            </form>
        </div>

        <div class="space-body">
            {{-- Own Lists --}}
            @php $ownLists = $ownLists ?? collect(); @endphp
            @if ($ownLists->isNotEmpty())
                <h3 style="color: rgba(255,255,255,0.5); font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">List Saya</h3>
                <div class="lists-grid" style="margin-bottom: 40px;">
                    @foreach ($ownLists as $list)
                        <article class="list-card">
                            @if ($list->cover_photo)
                                <div style="margin-bottom:8px;border-radius:8px;overflow:hidden;max-height:120px;">
                                    <img src="{{ asset('storage/'.$list->cover_photo) }}" alt="{{ $list->name }}" style="width:100%;height:120px;object-fit:cover;display:block;">
                                </div>
                            @endif
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
                            @if ($list->code)
                                <span style="display: inline-block; margin-top: 6px; font-size: 0.7rem; font-family: 'Courier New', monospace; color: rgba(255,255,255,0.4); background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); border-radius: 4px; padding: 2px 8px; letter-spacing: 1px;">{{ $list->code }}</span>
                            @endif

                            <div class="list-card-actions">
                                <a href="{{ route('lists.show', $list) }}" class="list-action-link">Lihat</a>
                                <a href="{{ route('your-space.lists.edit', $list) }}" class="list-action-link">Edit</a>
                                <form method="POST" action="{{ route('your-space.lists.destroy', $list) }}" class="list-delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="list-action-delete" onclick="return confirm('Hapus list \'{{ addslashes($list->name) }}\'?')">Hapus</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div style="margin-bottom: 40px;">
                    <x-space.empty icon="list" message="Belum ada list." :link="route('your-space.lists.create')" linkText="Buat list pertamamu" />
                </div>
            @endif

            {{-- Joined Lists --}}
            @php $joinedLists = $joinedLists ?? collect(); @endphp
            @if ($joinedLists->isNotEmpty())
                <h3 style="color: rgba(255,255,255,0.5); font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">List Yang Diikuti</h3>
                <div class="lists-grid">
                    @foreach ($joinedLists as $list)
                        <article class="list-card">
                            @if ($list->cover_photo)
                                <div style="margin-bottom:8px;border-radius:8px;overflow:hidden;max-height:120px;">
                                    <img src="{{ asset('storage/'.$list->cover_photo) }}" alt="{{ $list->name }}" style="width:100%;height:120px;object-fit:cover;display:block;">
                                </div>
                            @endif
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

                            <p style="font-size: 0.75rem; color: rgba(255,255,255,0.35);">Oleh {{ $list->user->name }}</p>

                            <div class="list-card-actions">
                                <a href="{{ route('lists.show', $list) }}" class="list-action-link">Lihat</a>
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
