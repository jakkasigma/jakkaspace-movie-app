<x-guest-layout>
    <div class="auth-form-wrap">
        <div class="auth-form-heading">
            <p class="auth-form-kicker">Login</p>
            <h2 class="auth-form-title">Selamat datang kembali.</h2>
            <p class="auth-form-desc">Masuk untuk membuka Your Space dan lanjut menjelajah Jakka Space.</p>
        </div>

        <x-auth-session-status class="auth-status-msg" :status="session('status')" />

        {{-- Google OAuth --}}
        <a href="{{ route('auth.google') }}" class="auth-google-btn">
            <svg class="auth-google-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Masuk dengan Google
        </a>

        <div class="auth-divider">
            <span class="auth-divider-line"></span>
            <span class="auth-divider-text">ATAU</span>
            <span class="auth-divider-line"></span>
        </div>

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="form-row">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com">
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-row">
                <label class="form-label" for="password">Password</label>
                <input id="password" type="password" name="password" class="form-input" required autocomplete="current-password" placeholder="Password akun kamu">
                @error('password') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="auth-form-row-inline">
                <label class="form-check-label">
                    <input type="checkbox" name="remember" class="form-checkbox">
                    Ingat saya
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link-muted">Lupa password?</a>
                @endif
            </div>

            <div class="auth-form-actions">
                <a href="{{ route('register') }}" class="auth-link-muted">Belum punya akun?</a>
                <button type="submit" class="form-submit">Masuk</button>
            </div>
        </form>
    </div>
</x-guest-layout>
