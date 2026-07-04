@extends('layouts.movie')

@section('title', 'Buat List — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <h1 class="space-page-title">BUAT LIST</h1>
                <p class="space-page-subtitle">Koleksi film dengan namamu sendiri.</p>
            </div>
        </header>

        <x-space.nav active="lists" />
        <x-space.tab-bar active="lists" />

        <div class="space-body">
            <div class="list-form-wrap">
                <form method="POST" action="{{ route('your-space.lists.store') }}" class="list-form">
                    @csrf

                    <div class="form-row">
                        <label class="form-label" for="name">Nama List</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            class="form-input"
                            value="{{ old('name') }}"
                            placeholder="Contoh: Film Favorit 2024"
                            maxlength="100"
                            required
                            autofocus
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
                            placeholder="Ceritakan isi list ini..."
                            maxlength="500"
                            rows="3"
                        >{{ old('description') }}</textarea>
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
                                {{ old('is_public', '1') ? 'checked' : '' }}
                            >
                            Publik — siapa saja bisa melihat list ini
                        </label>
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('your-space.lists') }}" class="form-cancel">Batal</a>
                        <button type="submit" class="form-submit">Buat List</button>
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
