@extends('admin.layout')

@section('title', 'Tambah Tema')
@section('subtitle', 'Buat theme pack baru')

@section('content')
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.themes.store') }}" class="admin-form">
            @csrf

            <div>
                <label class="admin-form-label">Nama Tema</label>
                <input type="text" name="name" class="admin-form-input" value="{{ old('name') }}" required>
                @error('name') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label class="admin-form-label">Slug</label>
                <input type="text" name="slug" class="admin-form-input" value="{{ old('slug') }}" required placeholder="contoh: my-custom-theme">
                @error('slug') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label class="admin-form-label">Avatar Border CSS</label>
                <textarea name="avatar_border_css" class="admin-form-input admin-form-textarea" required placeholder="linear-gradient(135deg, #hex, #hex)">{{ old('avatar_border_css') }}</textarea>
                @error('avatar_border_css') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label class="admin-form-label">Warna Aksen</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="color" name="accent_color_picker" id="accentPicker" value="{{ old('accent_color', '#e23636') }}" style="height:40px;width:60px;border-radius:6px;border:1px solid var(--border);background:transparent;cursor:pointer;">
                    <input type="text" name="accent_color" class="admin-form-input" id="accentInput" value="{{ old('accent_color', '#e23636') }}" required placeholder="#hex" style="max-width:120px;">
                    <span id="accentPreview" style="width:24px;height:24px;border-radius:50%;background:{{ old('accent_color', '#e23636') }};border:2px solid #000;display:inline-block;"></span>
                </div>
                @error('accent_color') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label class="admin-form-label">Badge Icon (emoji)</label>
                <input type="text" name="badge_icon" class="admin-form-input" value="{{ old('badge_icon') }}" placeholder="🦸" style="max-width:120px;">
                @error('badge_icon') <small style="color:var(--danger)">{{ $message }}</small> @enderror
            </div>

            <div>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width:18px;height:18px;">
                    <span class="admin-form-label" style="margin:0;">Aktif</span>
                </label>
            </div>

            <div>
                <label class="admin-form-label">Preview</label>
                <div id="previewContainer" style="display:flex;align-items:center;gap:16px;padding:16px;background:rgba(255,255,255,0.03);border-radius:8px;">
                    <div id="previewAvatar" class="admin-avatar-demo" style="background:{{ old('avatar_border_css', 'linear-gradient(135deg, #e23636, #f78f3f)') }}">
                        <div class="admin-avatar-inner" id="previewBadge">{{ old('badge_icon', '🦸') ?: 'J' }}</div>
                    </div>
                    <div>
                        <span id="previewName" style="color:{{ old('accent_color', '#e23636') }};font-weight:600;">Nama Tema</span>
                        <span id="previewBadgeIcon" style="margin-left:4px;">{{ old('badge_icon', '🦸') }}</span>
                    </div>
                </div>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">Simpan</button>
                <a href="{{ route('admin.themes.index') }}" class="admin-btn">Batal</a>
            </div>
        </form>
    </div>

    <script>
        const nameInput = document.querySelector('[name="name"]');
        const slugInput = document.querySelector('[name="slug"]');
        nameInput.addEventListener('input', function () {
            if (!slugInput.dataset.manual) {
                slugInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
            }
        });
        slugInput.addEventListener('input', function () { this.dataset.manual = '1'; });

        const accentPicker = document.getElementById('accentPicker');
        const accentInput = document.getElementById('accentInput');
        const accentPreview = document.getElementById('accentPreview');

        function syncAccent(val) {
            accentInput.value = val;
            accentPicker.value = val;
            accentPreview.style.background = val;
            document.getElementById('previewName').style.color = val;
        }
        accentPicker.addEventListener('input', function () { syncAccent(this.value); });
        accentInput.addEventListener('input', function () {
            if (/^#[0-9a-f]{3,6}$/i.test(this.value)) syncAccent(this.value);
        });

        const borderInput = document.querySelector('[name="avatar_border_css"]');
        borderInput.addEventListener('input', function () {
            document.getElementById('previewAvatar').style.background = this.value;
        });

        const badgeInput = document.querySelector('[name="badge_icon"]');
        badgeInput.addEventListener('input', function () {
            document.getElementById('previewBadge').textContent = this.value || 'J';
            document.getElementById('previewBadgeIcon').textContent = this.value || '';
        });
    </script>
@endsection
