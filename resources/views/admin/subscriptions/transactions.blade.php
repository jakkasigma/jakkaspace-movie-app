@extends('admin.layout')

@section('title', 'Transactions')
@section('subtitle', 'All subscription transactions')

@section('content')
    <div style="display:flex;gap:8px;margin-bottom:16px;align-items:center;">
        <form method="GET" style="display:flex;gap:8px;margin-left:auto;">
            <select name="action" class="admin-form-input" style="max-width:160px;" onchange="this.form.submit()">
                <option value="">Semua Aksi</option>
                <option value="subscribe" {{ $actionFilter === 'subscribe' ? 'selected' : '' }}>Langganan Baru</option>
                <option value="renew" {{ $actionFilter === 'renew' ? 'selected' : '' }}>Perpanjangan</option>
                <option value="upgrade" {{ $actionFilter === 'upgrade' ? 'selected' : '' }}>Upgrade</option>
                <option value="cancel" {{ $actionFilter === 'cancel' ? 'selected' : '' }}>Dibatalkan</option>
                <option value="admin_grant" {{ $actionFilter === 'admin_grant' ? 'selected' : '' }}>Grant Admin</option>
                <option value="admin_extend" {{ $actionFilter === 'admin_extend' ? 'selected' : '' }}>Extend Admin</option>
                <option value="redeem" {{ $actionFilter === 'redeem' ? 'selected' : '' }}>Redeem</option>
            </select>
            <input type="text" name="q" class="admin-form-input" placeholder="Cari user..." value="{{ $search }}" style="max-width:200px;">
        </form>
    </div>

    <div class="admin-card" style="padding:0;overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Aksi</th>
                    <th>Tier</th>
                    <th>Plan</th>
                    <th>Harga</th>
                    <th>Metode</th>
                    <th>Promo</th>
                    <th>Periode</th>
                    <th>Expires</th>
                    <th>Admin</th>
                    <th>Tgl</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $tx)
                    @php
                        $actionLabels = [
                            'subscribe' => ['Langganan Baru', '#4caf50'],
                            'renew' => ['Perpanjangan', '#2196f3'],
                            'upgrade' => ['Upgrade', '#ff9800'],
                            'cancel' => ['Dibatalkan', '#f44336'],
                            'admin_grant' => ['Grant', '#9c27b0'],
                            'admin_extend' => ['Extend', '#9c27b0'],
                            'redeem' => ['Redeem', '#00bcd4'],
                        ];
                        [$actionLabel, $actionColor] = $actionLabels[$tx->action] ?? [$tx->action, 'var(--muted)'];
                        $tierLabel = $tx->tier === 'plus_plus' ? 'Plus+' : ($tx->tier === 'plus' ? 'Plus' : $tx->tier);
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $tx->user?->name ?? 'Deleted' }}</strong>
                            <br><span style="font-size:0.75rem;color:var(--muted);">{{ $tx->user?->email ?? '-' }}</span>
                        </td>
                        <td><span style="color:{{ $actionColor }};font-weight:600;">{{ $actionLabel }}</span></td>
                        <td>{{ $tierLabel }}</td>
                        <td style="font-size:0.8rem;">{{ $tx->plan?->name ?? '-' }}</td>
                        <td>{{ $tx->price > 0 ? 'Rp'.number_format($tx->price, 0, ',', '.') : 'Gratis' }}</td>
                        <td style="text-transform:capitalize;">{{ $tx->payment_method ?? '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $tx->promo_code ?? '-' }}</td>
                        <td>{{ $tx->period_days }} hari</td>
                        <td style="font-size:0.8rem;white-space:nowrap;">{{ $tx->expires_at?->format('d M Y') ?? '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $tx->admin?->name ?? '-' }}</td>
                        <td style="font-size:0.78rem;white-space:nowrap;">{{ $tx->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @if ($tx->notes)
                        <tr>
                            <td colspan="11" style="padding:0 12px 8px;font-size:0.75rem;color:var(--muted);font-style:italic;">
                                {{ $tx->notes }}
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center;padding:40px;color:var(--muted);font-size:0.9rem;">
                            Belum ada transaksi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($transactions->hasPages())
        <div class="admin-pagination">{{ $transactions->links() }}</div>
    @endif
@endsection
