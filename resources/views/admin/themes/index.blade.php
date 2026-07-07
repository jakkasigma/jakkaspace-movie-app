@extends('admin.layout')

@section('title', 'Themes')
@section('subtitle', 'Kelola tema theme pack untuk pengguna Plus')

@section('content')
    <div style="margin-bottom:16px;">
        <a href="{{ route('admin.themes.create') }}" class="admin-btn admin-btn-primary">+ Tambah Tema</a>
    </div>

    <div class="admin-card" style="padding:0;overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Aksen</th>
                    <th>Badge</th>
                    <th>Active</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($themes as $theme)
                    <tr>
                        <td>{{ $theme->id }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="admin-avatar-demo" style="background:{{ $theme->avatar_border_css }}">
                                    <div class="admin-avatar-inner">{{ $theme->badge_icon ?: 'J' }}</div>
                                </div>
                                {{ $theme->name }}
                            </div>
                        </td>
                        <td><code style="font-size:0.8rem;color:var(--muted)">{{ $theme->slug }}</code></td>
                        <td><span style="color:{{ $theme->accent_color }}">{{ $theme->accent_color }}</span></td>
                        <td>{{ $theme->badge_icon }}</td>
                        <td>{{ $theme->is_active ? '✅' : '❌' }}</td>
                        <td>
                            <a href="{{ route('admin.themes.edit', $theme) }}" class="admin-btn admin-btn-sm">✏️ Edit</a>
                            <form method="POST" action="{{ route('admin.themes.destroy', $theme) }}" style="display:inline" onsubmit="return confirm('Hapus tema {{ $theme->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">🗑 Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:32px;color:var(--muted);">Belum ada tema.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($themes->hasPages())
        <div class="admin-pagination">{{ $themes->links() }}</div>
    @endif
@endsection
