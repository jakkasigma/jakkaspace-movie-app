<x-guest-layout>
    <div class="auth-form-wrap">
        <div class="auth-form-heading">
            <p class="auth-form-kicker">Password Baru</p>
            <h2 class="auth-form-title">Buat password baru.</h2>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-row">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" name="email" class="form-input" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-row">
                <label class="form-label" for="password">Password Baru</label>
                <input id="password" type="password" name="password" class="form-input" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                @error('password') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="form-row">
                <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-input" required autocomplete="new-password">
                @error('password_confirmation') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="auth-form-actions">
                <button type="submit" class="form-submit">Reset Password</button>
            </div>
        </form>
    </div>
</x-guest-layout>
