<div class="settings-danger-wrap">
    <button
        type="button"
        class="settings-danger-btn"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Hapus Akun</button>
</div>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" class="settings-modal-form">
        @csrf
        @method('delete')

        <h2 class="settings-modal-title">Hapus akun ini?</h2>
        <p class="settings-modal-desc">Semua data termasuk diary, review, watchlist, dan lists akan dihapus permanen dan tidak bisa dipulihkan.</p>

        <div class="form-row">
            <label class="form-label sr-only" for="delete_password">Password</label>
            <input
                id="delete_password"
                type="password"
                name="password"
                class="form-input"
                placeholder="Konfirmasi dengan password"
            >
            @error('password', 'userDeletion')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="settings-modal-footer">
            <button type="button" class="settings-modal-cancel" x-on:click="$dispatch('close')">Batal</button>
            <button type="submit" class="settings-danger-btn settings-danger-btn-sm">Ya, hapus akun</button>
        </div>
    </form>
</x-modal>
