@extends('admin.layout')

@section('title', 'Users')
@section('subtitle', 'Manage users')

@section('content')
    {{-- Stats --}}
    <div class="admin-grid" style="margin-bottom:24px;">
        <div class="admin-card">
            <span class="admin-stat-value">{{ $totalUsers }}</span>
            <span class="admin-stat-label">Total Users</span>
        </div>
        <div class="admin-card">
            <span class="admin-stat-value">{{ $totalBanned }}</span>
            <span class="admin-stat-label">Banned</span>
        </div>
        <div class="admin-card">
            <span class="admin-stat-value">{{ $totalAdmin }}</span>
            <span class="admin-stat-label">Admin</span>
        </div>
    </div>

    {{-- Filter + Search --}}
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-sm {{ ! $filter ? 'admin-btn-primary' : '' }}">All</a>
        <a href="{{ route('admin.users.index', ['filter' => 'plus']) }}" class="admin-btn admin-btn-sm {{ $filter === 'plus' ? 'admin-btn-primary' : '' }}">Plus</a>
        <a href="{{ route('admin.users.index', ['filter' => 'admin']) }}" class="admin-btn admin-btn-sm {{ $filter === 'admin' ? 'admin-btn-primary' : '' }}">Admin</a>
        <a href="{{ route('admin.users.index', ['filter' => 'banned']) }}" class="admin-btn admin-btn-sm {{ $filter === 'banned' ? 'admin-btn-primary' : '' }}">Banned</a>

        <form method="GET" style="margin-left:auto;">
            @if ($filter)
                <input type="hidden" name="filter" value="{{ $filter }}">
            @endif
            <input type="text" name="q" class="admin-form-input" placeholder="Cari user..." value="{{ $search }}" style="max-width:250px;">
        </form>
    </div>

    {{-- Table --}}
    <div class="admin-card" style="padding:0;overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Tier</th>
                    <th>Admin</th>
                    <th>Status</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ '@' . ($user->username ?? '-') }}</td>
                        <td>
                            @if ($user->isPlus())
                                <span style="color:#4caf50;">Plus</span>
                            @else
                                Free
                            @endif
                        </td>
                        <td>{{ $user->is_admin ? '✅' : '—' }}</td>
                        <td>
                            @if ($user->is_banned)
                                <span style="color:var(--danger);">Banned</span>
                            @else
                                <span style="color:#4caf50;">Active</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            @if ($user->is_banned)
                                <form method="POST" action="{{ route('admin.users.unban', $user) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="admin-btn admin-btn-sm" style="border-color:#4caf50;color:#4caf50;">Unban</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.ban', $user) }}" style="display:inline;" onsubmit="return confirm('Ban {{ $user->name }}?')">
                                    @csrf
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Ban</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:32px;color:var(--muted);">Tidak ada user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="admin-pagination">{{ $users->links() }}</div>
    @endif
@endsection
