@extends('admin.layout')

@section('title', 'Subscriptions')
@section('subtitle', 'Manage Plus subscriptions & grant access')

@section('content')
    {{-- Stats --}}
    <div class="admin-grid" style="margin-bottom:24px;">
        <div class="admin-card">
            <span class="admin-stat-value">{{ $totalActive }}</span>
            <span class="admin-stat-label">Subscriber Aktif</span>
        </div>
        <div class="admin-card">
            <span class="admin-stat-value">{{ $totalExpired }}</span>
            <span class="admin-stat-label">Expired</span>
        </div>
        <div class="admin-card">
            <span class="admin-stat-value">Rp{{ number_format($monthlyRevenue, 0, ',', '.') }}</span>
            <span class="admin-stat-label">Estimasi Revenue/bulan</span>
        </div>
    </div>

    {{-- Actions bar --}}
    <div style="display:flex;gap:8px;margin-bottom:16px;align-items:center;flex-wrap:wrap;">
        <button type="button" class="admin-btn admin-btn-primary" onclick="showGrantModal()">+ Grant Subscription</button>
        <form method="GET" style="display:flex;gap:8px;margin-left:auto;">
            <select name="tier" class="admin-form-input" style="max-width:140px;" onchange="this.form.submit()">
                <option value="">Semua Tier</option>
                <option value="plus" {{ $tierFilter === 'plus' ? 'selected' : '' }}>Plus</option>
                <option value="plus_plus" {{ $tierFilter === 'plus_plus' ? 'selected' : '' }}>Plus+</option>
            </select>
            <input type="text" name="q" class="admin-form-input" placeholder="Cari user..." value="{{ $search }}" style="max-width:200px;">
        </form>
    </div>

    {{-- Table --}}
    <div class="admin-card" style="padding:0;overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Tier</th>
                    <th>Sejak</th>
                    <th>Expires</th>
                    <th>Sisa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $sub)
                    @php $isActive = $sub->expires_at && now()->lessThan($sub->expires_at); @endphp
                    <tr>
                        <td><strong>{{ $sub->name }}</strong></td>
                        <td style="color:var(--muted);font-size:0.8rem;">{{ $sub->email }}</td>
                        <td>
                            @if ($sub->subscription_tier === 'plus_plus')
                                <span style="background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;padding:1px 8px;border-radius:4px;font-size:0.72rem;font-weight:600;">Plus+</span>
                            @else
                                <span style="background:rgba(255,255,255,0.1);color:#fff;padding:1px 8px;border-radius:4px;font-size:0.72rem;font-weight:600;">Plus</span>
                            @endif
                        </td>
                        <td style="font-size:0.8rem;">{{ $sub->subscribed_at?->format('d M Y') ?? '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $sub->expires_at?->format('d M Y') ?? '-' }}</td>
                        <td>
                            @if ($isActive)
                                <span style="color:#4caf50;font-weight:600;">{{ (int) now()->diffInDays($sub->expires_at, true) }} hari</span>
                            @else
                                <span style="color:var(--danger);font-weight:600;">Expired</span>
                            @endif
                        </td>
                        <td>
                            @if ($isActive)
                                <form method="POST" action="{{ route('admin.subscriptions.extend', $sub) }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="days" value="30">
                                    <button type="submit" class="admin-btn admin-btn-sm" title="Extend 30 hari">+30d</button>
                                </form>
                                <form method="POST" action="{{ route('admin.subscriptions.cancel', $sub) }}" style="display:inline;" onsubmit="return confirm('Cancel subscription for {{ $sub->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-danger">Cancel</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.subscriptions.grant') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $sub->id }}">
                                    <input type="hidden" name="tier" value="plus">
                                    <input type="hidden" name="period" value="monthly">
                                    <button type="submit" class="admin-btn admin-btn-sm admin-btn-primary">Renew</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--muted);font-size:0.9rem;">
                            Belum ada subscription.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($subscriptions->hasPages())
        <div class="admin-pagination">{{ $subscriptions->links() }}</div>
    @endif

    {{-- Grant Modal --}}
    <div class="admin-modal-overlay" id="grantModal" style="display:none;" onclick="if(event.target===this)hideGrantModal()">
        <div class="admin-modal">
            <h2 class="admin-modal-title">Grant Subscription</h2>
            <form method="POST" action="{{ route('admin.subscriptions.grant') }}">
                @csrf
                <label class="admin-form-label" style="margin-bottom:6px;display:block;">User</label>
                <select name="user_id" class="admin-form-input" required style="margin-bottom:16px;">
                    <option value="">— Pilih User —</option>
                    @foreach ($freeUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>

                <label class="admin-form-label" style="margin-bottom:6px;display:block;">Tier</label>
                <select name="tier" class="admin-form-input" required style="margin-bottom:16px;">
                    <option value="plus">Plus</option>
                    <option value="plus_plus">Plus+</option>
                </select>

                <label class="admin-form-label" style="margin-bottom:6px;display:block;">Periode</label>
                <select name="period" class="admin-form-input" required style="margin-bottom:8px;">
                    <option value="monthly">1 Bulan</option>
                    <option value="yearly">1 Tahun (hemat 2 bulan)</option>
                </select>

                <div class="admin-modal-actions">
                    <button type="submit" class="admin-btn admin-btn-primary">Grant</button>
                    <button type="button" class="admin-btn" onclick="hideGrantModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showGrantModal() { document.getElementById('grantModal').style.display = 'flex'; }
        function hideGrantModal() { document.getElementById('grantModal').style.display = 'none'; }
    </script>
@endsection
