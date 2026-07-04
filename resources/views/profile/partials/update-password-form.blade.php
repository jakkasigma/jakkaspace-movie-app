<form method="post" action="{{ route('password.update') }}" class="settings-form">
    @csrf
    @method('put')

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

    <div class="form-row">
        <label class="form-label" for="update_password_password">Password Baru</label>
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
        <button type="submit" class="form-submit">Ubah Password</button>

        @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2500)"
                class="settings-saved-msg"
            >Password berhasil diubah.</p>
        @endif
    </div>
</form>
