<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

{{-- Avatar upload — standalone, terpisah dari form utama --}}
<div
    x-data="avatarCropper()"
    data-current-avatar="{{ $user->avatar_url ?? '' }}"
    class="form-row"
>
    <label class="form-label">Foto Profil</label>
    <div class="settings-avatar-wrap">
        @if ($user->avatar_url)
            <img x-ref="avatarPreview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="settings-avatar-preview">
        @else
            <div x-ref="avatarPreview" class="settings-avatar-preview settings-avatar-placeholder">
                {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
            </div>
        @endif
        <div class="settings-avatar-input-wrap">
            <label for="avatar" class="settings-avatar-btn">Pilih Foto</label>
            <input
                id="avatar"
                type="file"
                accept="image/*"
                class="settings-avatar-input"
                @change="fileSelected"
            >
            <p class="form-hint">JPG, PNG, WebP. Foto akan di-crop 1:1 dan dikompres.</p>
        </div>
    </div>
    <template x-if="error">
        <p class="form-error" x-text="error"></p>
    </template>

    {{-- Crop modal --}}
    <template x-teleport="body">
        <div x-show="showModal" class="avatar-crop-overlay" x-cloak>
            <div class="avatar-crop-modal" @click.outside="cancel">
                <h3 class="avatar-crop-title">Crop Foto Profil</h3>
                <div class="avatar-crop-container">
                    <img x-ref="cropImage" :src="imageUrl" alt="Crop preview">
                </div>
                <div class="avatar-crop-controls">
                    <button type="button" class="avatar-crop-btn" @click="zoomOut" title="Perkecil">−</button>
                    <button type="button" class="avatar-crop-btn" @click="reset" title="Reset">↺</button>
                    <button type="button" class="avatar-crop-btn" @click="zoomIn" title="Perbesar">+</button>
                </div>
                <div class="avatar-crop-actions">
                    <button type="button" class="avatar-crop-cancel" @click="cancel">Batal</button>
                    <button type="button" class="avatar-crop-save" @click="save" :disabled="loading">
                        <span x-show="!loading">Simpan Foto</span>
                        <span x-show="loading" class="avatar-crop-spinner"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<form method="post" action="{{ route('profile.update') }}" class="settings-form">
    @csrf
    @method('patch')

    <div class="form-row">
        <label class="form-label" for="name">Nama</label>
        <input
            id="name"
            type="text"
            name="name"
            class="form-input"
            value="{{ old('name', $user->name) }}"
            required
            autocomplete="name"
        >
        @error('name')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-row">
        <label class="form-label" for="username">
            Username
            <span class="form-optional">— tampil di profil publik (/@username)</span>
        </label>
        <div class="settings-input-prefix-wrap">
            <span class="settings-input-prefix">@</span>
            <input
                id="username"
                type="text"
                name="username"
                class="form-input settings-input-with-prefix"
                value="{{ old('username', $user->username) }}"
                placeholder="jakkauser"
                maxlength="32"
                autocomplete="off"
            >
        </div>
        @error('username')
            <p class="form-error">{{ $message }}</p>
        @enderror
        <p class="form-hint">Hanya huruf, angka, dan underscore. Maks 32 karakter.</p>
    </div>

    <div class="form-row">
        <label class="form-label" for="bio">Bio <span class="form-optional">(opsional)</span></label>
        <textarea
            id="bio"
            name="bio"
            class="form-textarea"
            placeholder="Ceritakan sedikit tentang dirimu..."
            maxlength="300"
            rows="3"
        >{{ old('bio', $user->bio) }}</textarea>
        @error('bio')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-row">
        <label class="form-label">Privasi Akun</label>
        <label class="settings-toggle-row">
            <input type="hidden" name="is_private" value="0">
            <input type="checkbox" name="is_private" value="1" class="settings-toggle-input" {{ old('is_private', $user->is_private) ? 'checked' : '' }}>
            <span class="settings-toggle-track">
                <span class="settings-toggle-thumb"></span>
            </span>
            <span class="settings-toggle-label">
                Akun privat
                <span class="form-hint">Profil tidak akan muncul di pencarian dan tidak bisa di-follow tanpa persetujuan.</span>
            </span>
        </label>
        @error('is_private')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-row">
        <label class="form-label" for="email">Email</label>
        <input
            id="email"
            type="email"
            name="email"
            class="form-input"
            value="{{ old('email', $user->email) }}"
            required
            autocomplete="username"
        >
        @error('email')
            <p class="form-error">{{ $message }}</p>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="settings-verify-notice">
                <p>Email belum diverifikasi.
                    <button form="send-verification" class="settings-verify-link">Kirim ulang email verifikasi</button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="settings-verify-sent">Link verifikasi berhasil dikirim.</p>
                @endif
            </div>
        @endif
    </div>

    <div class="settings-form-footer">
        <button type="submit" class="form-submit">Simpan</button>

        @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2500)"
                class="settings-saved-msg"
            >Tersimpan.</p>
        @endif
    </div>
</form>
