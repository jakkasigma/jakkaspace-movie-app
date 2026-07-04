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
                <form method="POST" action="{{ route('your-space.lists.update', $list) }}" class="list-form">
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
