@extends('layouts.movie')

@section('title', 'Pengaturan — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <a href="{{ route('profile.show', auth()->user()->username) }}" class="profile-back-link">← Kembali ke profil</a>
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">PENGATURAN</h1>
                    <p class="space-page-subtitle">Kelola akun dan profil publikmu.</p>
                </div>
            </div>
        </header>

        <div class="space-body">
            <div class="settings-wrap">

                {{-- Profile Info --}}
                <section class="settings-section">
                    <header class="settings-section-header">
                        <h2 class="settings-section-title">Informasi Profil</h2>
                        <p class="settings-section-desc">Nama, username, bio, dan email yang tampil di profilmu.</p>
                    </header>

                    @include('profile.partials.update-profile-information-form')
                </section>

                {{-- Password --}}
                <section class="settings-section">
                    <header class="settings-section-header">
                        <h2 class="settings-section-title">Ubah Password</h2>
                        <p class="settings-section-desc">Gunakan password yang panjang dan acak agar akunmu aman.</p>
                    </header>

                    @include('profile.partials.update-password-form')
                </section>

                {{-- Linked Accounts --}}
                <section class="settings-section">
                    <header class="settings-section-header">
                        <h2 class="settings-section-title">Akun Terhubung</h2>
                        <p class="settings-section-desc">Kelola layanan yang terhubung ke akun Jakka Space-mu.</p>
                    </header>

                    @include('profile.partials.linked-accounts-form')
                </section>

                {{-- Android App --}}
                @if (str_contains(request()->userAgent(), 'Android'))
                    <section class="settings-section">
                        <header class="settings-section-header">
                            <h2 class="settings-section-title">📱 Aplikasi Android</h2>
                            <p class="settings-section-desc">Pasang Jakka Space sebagai aplikasi Android. Download sekali, update konten otomatis.</p>
                        </header>

                        <div class="settings-app-download">
                            <a href="{{ asset('apk/jakkaspace.apk') }}" class="form-submit" download>
                                📥 Download APK (4.5 MB)
                            </a>
                            <p class="form-hint" style="margin-top:10px;">Aktifkan "Install dari sumber tidak dikenal" di pengaturan HP sebelum install.</p>
                        </div>
                    </section>
                @endif

                {{-- Delete Account --}}
                <section class="settings-section settings-section-danger">
                    <header class="settings-section-header">
                        <h2 class="settings-section-title settings-section-title-danger">Hapus Akun</h2>
                        <p class="settings-section-desc">Setelah akun dihapus, semua data tidak bisa dipulihkan.</p>
                    </header>

                    @include('profile.partials.delete-user-form')
                </section>

            </div>
        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
