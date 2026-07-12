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

                {{-- Aplikasi — Android (APK) / iOS/Windows (PWA) --}}
                @php $ua = request()->userAgent(); @endphp

                @if (str_contains($ua, 'Android'))
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
                @elseif (preg_match('/iPhone|iPad|iPod/', $ua))
                    <section class="settings-section">
                        <header class="settings-section-header">
                            <h2 class="settings-section-title">📱 Pasang Aplikasi</h2>
                            <p class="settings-section-desc">Jakka Space bisa dipasang di layar utama iPhone/iPad-mu.</p>
                        </header>

                        <div class="settings-app-download">
                            <ol style="text-align:left;color:rgba(255,255,255,0.7);font-size:0.85rem;margin:0 0 12px;padding-left:20px;line-height:2;">
                                <li>Tap tombol <strong>Share</strong> (kotak + panah atas)</li>
                                <li>Scroll ke bawah, tap <strong>Add to Home Screen</strong></li>
                                <li>Tap <strong>Add</strong> di pojok kanan atas</li>
                            </ol>
                            <p class="form-hint">Aplikasi akan muncul di layar utama seperti app native.</p>
                        </div>
                    </section>
                @elseif (str_contains($ua, 'Windows') || str_contains($ua, 'Mac') || str_contains($ua, 'Linux'))
                    <section class="settings-section">
                        <header class="settings-section-header">
                            <h2 class="settings-section-title">🖥️ Pasang Aplikasi</h2>
                            <p class="settings-section-desc">Jakka Space bisa dipasang sebagai PWA di desktop.</p>
                        </header>

                        <div class="settings-app-download">
                            <ol style="text-align:left;color:rgba(255,255,255,0.7);font-size:0.85rem;margin:0 0 12px;padding-left:20px;line-height:2;">
                                <li>Klik ikon <strong>Install</strong> di address bar <span style="font-size:1.2rem;">⊕</span></li>
                                <li>Atau buka menu ⋮ → <strong>Cast, save, and share</strong> → <strong>Install page as app</strong></li>
                                <li>Klik <strong>Install</strong></li>
                            </ol>
                            <p class="form-hint">Aplikasi akan muncul di taskbar/dock seperti app native.</p>
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
