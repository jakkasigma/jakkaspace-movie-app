@extends('layouts.movie')

@section('title', 'Edit List — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <h1 class="space-page-title">EDIT LIST</h1>
                <p class="space-page-subtitle">{{ $list->name }}</p>
            </div>
        </header>

        <x-space.nav active="lists" />
        <x-space.tab-bar active="lists" />

        <div class="space-body">
            <div class="list-form-wrap">
                <form method="POST" action="{{ route('your-space.lists.update', $list) }}" class="list-form" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="form-row">
                        <label class="form-label" for="name">Nama List</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            class="form-input"
                            value="{{ old('name', $list->name) }}"
                            maxlength="100"
                            required
                        >
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label class="form-label" for="description">Deskripsi <span class="form-optional">(opsional)</span></label>
                        <textarea
                            id="description"
                            name="description"
                            class="form-textarea"
                            maxlength="500"
                            rows="3"
                        >{{ old('description', $list->description) }}</textarea>
                        @error('description')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label class="form-check-label">
                            <input
                                type="checkbox"
                                name="is_public"
                                value="1"
                                class="form-checkbox"
                                {{ old('is_public', $list->is_public) ? 'checked' : '' }}
                            >
                            Publik — siapa saja bisa melihat list ini
                        </label>
                    </div>

                    <div class="form-row">
                        <label class="form-label" for="cover_photo">Cover List <span class="form-optional">(opsional, Plus+ exclusive)</span></label>
                        @if ($canUploadCover)
                            @if ($list->cover_photo)
                                <div style="margin-bottom:8px;">
                                    <img src="{{ asset('storage/'.$list->cover_photo) }}" alt="Cover" style="max-width:200px;max-height:120px;border-radius:8px;border:1px solid rgba(255,255,255,0.1);">
                                </div>
                            @endif
                            <input
                                id="cover_photo"
                                type="file"
                                name="cover_photo"
                                class="form-input"
                                accept="image/jpeg,image/png,image/webp"
                            >
                            <p style="font-size:0.72rem;color:var(--muted);margin-top:4px;">Kosongkan jika tidak ingin mengubah cover. Format: jpg, jpeg, png, webp. Maks 2MB.</p>
                        @else
                            <p style="font-size:0.82rem;color:var(--muted);padding:8px 0;">
                                💎 Hanya pelanggan <strong>Plus+</strong> yang bisa upload cover list.
                                <a href="{{ route('plus') }}" style="color:var(--accent);">Upgrade sekarang →</a>
                            </p>
                        @endif
                        @error('cover_photo')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('lists.show', $list) }}" class="form-cancel">Batal</a>
                        <button type="submit" class="form-submit">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
