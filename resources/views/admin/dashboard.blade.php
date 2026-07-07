@extends('admin.layout')

@section('title', 'Dashboard')
@section('subtitle', 'Overview Jakkaspace')

@section('content')
    <div class="admin-grid" style="margin-bottom:32px;">
        <div class="admin-card">
            <span class="admin-stat-value">{{ $totalUsers }}</span>
            <span class="admin-stat-label">Total Users</span>
        </div>
        <div class="admin-card">
            <span class="admin-stat-value">{{ $totalPlus }}</span>
            <span class="admin-stat-label">Plus Active</span>
        </div>
        <div class="admin-card">
            <span class="admin-stat-value">Rp{{ number_format($monthlyRevenue, 0, ',', '.') }}</span>
            <span class="admin-stat-label">Estimasi Revenue/bulan</span>
        </div>
        <div class="admin-card">
            <span class="admin-stat-value">{{ $totalThemes }}</span>
            <span class="admin-stat-label">Total Themes</span>
        </div>
    </div>

    @if ($recentPlusUsers->isNotEmpty())
        <div class="admin-card">
            <h2 style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;letter-spacing:0.05em;margin-bottom:16px;">Plus Terbaru</h2>
            <div class="admin-table-wrap"><table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Sejak</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentPlusUsers as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->subscribed_at?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $u->expires_at?->format('d M Y') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>
    @endif
@endsection
