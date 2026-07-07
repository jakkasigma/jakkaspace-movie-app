@extends('admin.layout')

@section('title', 'Edit Tema')
@section('subtitle', $theme->name)

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.themes.update', $theme) }}" class="admin-form">
            @csrf
            @method('PUT')

            <div>
                <label class="admin-form-label">Nama Tema</label>
                <input type="text" name="name" class="admin-form-input" value="{{ old('name', $theme->name) }}" required>
                @error('name') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label class="admin-form-label">Slug</label>
                <input type="text" name="slug" class="admin-form-input" value="{{ old('slug', $theme->slug) }}" required placeholder="contoh: my-custom-theme">
                @error('slug') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label class="admin-form-label">Avatar Border CSS</label>
                <textarea name="avatar_border_css" class="admin-form-input admin-form-textarea" required>{{ old('avatar_border_css', $theme->avatar_border_css) }}</textarea>
                @error('avatar_border_css') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label class="admin-form-label">Warna Aksen</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="color" id="accentPicker" value="{{ old('accent_color', $theme->accent_color) }}" style="height:40px;width:60px;border-radius:6px;border:1px solid var(--border);background:transparent;cursor:pointer;">
                    <input type="text" name="accent_color" class="admin-form-input" id="accentInput" value="{{ old('accent_color', $theme->accent_color) }}" required placeholder="#hex" style="max-width:120px;">
                    <span id="accentPreview" style="width:24px;height:24px;border-radius:50%;background:{{ $theme->accent_color }};border:2px solid #000;display:inline-block;"></span>
                </div>
                @error('accent_color') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label class="admin-form-label">Badge Icon (emoji)</label>
                <input type="text" name="badge_icon" class="admin-form-input" value="{{ old('badge_icon', $theme->badge_icon) }}" placeholder="🦸" style="max-width:120px;">
                @error('badge_icon') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $theme->is_active) ? 'checked' : '' }} style="width:18px;height:18px;">
                    <span class="admin-form-label" style="margin:0;">Aktif</span>
                </label>
            </div>

            <div>
                <label class="admin-form-label">Preview</label>
                <div style="display:flex;align-items:center;gap:16px;padding:16px;background:rgba(255,255,255,0.03);border-radius:8px;">
                    <div class="admin-avatar-demo" id="previewAvatar" style="background:{{ $theme->avatar_border_css }}">
                        <div class="admin-avatar-inner" id="previewBadge">{{ $theme->badge_icon ?: 'J' }}</div>
                    </div>
                    <div>
                        <span id="previewName" style="color:{{ $theme->accent_color }};font-weight:600;">{{ $theme->name }}</span>
                        <span id="previewBadgeIcon" style="margin-left:4px;">{{ $theme->badge_icon }}</span>
                    </div>
                </div>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Update</button>
                <a href="{{ route('admin.themes.index') }}" class="admin-btn">Batal</a>
                <form method="POST" action="{{ route('admin.themes.destroy', $theme) }}" style="margin-left:auto;" onsubmit="return confirm('Hapus tema {{ $theme->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="admin-btn admin-btn-danger">Hapus</button>
                </form>
            </div>
        </form>
    </div>

    <script>
        const borderInput = document.querySelector('[name="avatar_border_css"]');
        borderInput.addEventListener('input', function () {
            document.getElementById('previewAvatar').style.background = this.value;
        });

        const accentPicker = document.getElementById('accentPicker');
        const accentInput = document.getElementById('accentInput');
        const accentPreview = document.getElementById('accentPreview');
        function syncAccent(val) {
            accentInput.value = val; accentPicker.value = val;
            accentPreview.style.background = val;
            document.getElementById('previewName').style.color = val;
        }
        accentPicker.addEventListener('input', function () { syncAccent(this.value); });
        accentInput.addEventListener('input', function () {
            if (/^#[0-9a-f]{3,6}$/i.test(this.value)) syncAccent(this.value);
        });

        const badgeInput = document.querySelector('[name="badge_icon"]');
        badgeInput.addEventListener('input', function () {
            document.getElementById('previewBadge').textContent = this.value || 'J';
            document.getElementById('previewBadgeIcon').textContent = this.value || '';
        });
    </script>
@endsection
