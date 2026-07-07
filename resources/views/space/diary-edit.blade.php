@extends('layouts.movie')

@section('title', 'Edit Diary — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <a href="{{ route('your-space.diary') }}" class="profile-back-link">← Diary</a>
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">EDIT DIARY</h1>
                    <p class="space-page-subtitle">{{ $entry->movie_title ?? 'Film #' . $entry->tmdb_id }}</p>
                </div>
            </div>
        </header>

        <x-space.nav active="diary" />
        <x-space.tab-bar active="diary" />

        <div class="space-body">
            <div class="list-form-wrap">
                <form method="POST" action="{{ route('your-space.diary.update', $entry) }}" class="list-form">
                    @csrf @method('PUT')

                    <div class="form-row">
                        <label class="form-label" for="watched_at">Tanggal Nonton</label>
                        <input id="watched_at" type="date" name="watched_at" class="form-input" value="{{ old('watched_at', $entry->watched_at->format('Y-m-d')) }}" required>
                        @error('watched_at')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label class="form-label" for="mood">Mood <span class="form-optional">(opsional)</span></label>
                        <input id="mood" type="text" name="mood" class="form-input" value="{{ old('mood', $entry->mood) }}" placeholder="Contoh: 😊, 🎬, 🤯" maxlength="50">
                        @error('mood')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label class="form-label" for="notes">Catatan <span class="form-optional">(opsional)</span></label>
                        <textarea id="notes" name="notes" class="form-textarea" rows="5" maxlength="5000" placeholder="Ceritakan pengalaman menontonmu...">{{ old('notes', $entry->notes) }}</textarea>
                        @error('notes')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label class="form-check-label">
                            <input type="checkbox" name="is_rewatch" value="1" class="form-checkbox" {{ old('is_rewatch', $entry->is_rewatch) ? 'checked' : '' }}>
                            Rewatch — sudah pernah nonton sebelumnya
                        </label>
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('your-space.diary') }}" class="form-cancel">Batal</a>
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
