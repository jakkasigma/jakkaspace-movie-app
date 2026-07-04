<x-guest-layout>
    <div class="auth-form-wrap">
        <div class="auth-form-heading">
            <p class="auth-form-kicker">Reset Password</p>
            <h2 class="auth-form-title">Lupa password?</h2>
            <p class="auth-form-desc">Masukkan emailmu dan kami akan kirim link untuk membuat password baru.</p>
        </div>

        <x-auth-session-status class="auth-status-msg" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf

            <div class="form-row">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com">
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="auth-form-actions">
                <a href="{{ route('login') }}" class="auth-link-muted">← Kembali ke login</a>
                <button type="submit" class="form-submit">Kirim Link</button>
            </div>
        </form>
    </div>
</x-guest-layout>
