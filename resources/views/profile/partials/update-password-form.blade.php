<form method="post" action="{{ route('password.update') }}" class="settings-form">
    @csrf
    @method('put')

    @if ($user->has_password)
        <div class="form-row">
            <label class="form-label" for="update_password_current_password">Password Saat Ini</label>
            <input
                id="update_password_current_password"
                type="password"
                name="current_password"
                class="form-input"
                autocomplete="current-password"
            >
            @error('current_password', 'updatePassword')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
    @else
        <div class="form-notice">
            <p>Kamu belum punya password karena daftar via Google. Set password sekarang agar bisa login pakai email juga.</p>
        </div>
    @endif

    <div class="form-row">
        <label class="form-label" for="update_password_password">
            {{ $user->has_password ? 'Password Baru' : 'Password' }}
        </label>
        <input
            id="update_password_password"
            type="password"
            name="password"
            class="form-input"
            autocomplete="new-password"
            placeholder="Minimal 8 karakter"
        >
        @error('password', 'updatePassword')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-row">
        <label class="form-label" for="update_password_password_confirmation">Konfirmasi Password Baru</label>
        <input
            id="update_password_password_confirmation"
            type="password"
            name="password_confirmation"
            class="form-input"
            autocomplete="new-password"
        >
        @error('password_confirmation', 'updatePassword')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="settings-form-footer">
        <button type="submit" class="form-submit">
            {{ $user->has_password ? 'Ubah Password' : 'Set Password' }}
        </button>

        @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2500)"
                class="settings-saved-msg"
            >Password berhasil diubah.</p>
        @endif

        @if (session('status') === 'password-set')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2500)"
                class="settings-saved-msg"
            >Password berhasil dibuat. Kamu sekarang bisa login pakai email juga!</p>
        @endif
    </div>
</form>
