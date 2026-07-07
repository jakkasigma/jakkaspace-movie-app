@extends('admin.layout')

@section('title', "Kode: {$code->code} — Admin")

@section('content')
<div style="margin-bottom:20px;">
    <a href="{{ route('admin.promo-redeem.index', ['tab' => 'redeem']) }}" style="color:var(--muted);text-decoration:none;font-size:0.82rem;">&larr; Kembali</a>
</div>

<div class="admin-card" style="margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
        <div>
            <h2 style="color:#fff;font-size:1.1rem;font-weight:700;margin:0 0 4px;font-family:'Courier New',monospace;">{{ $code->code }}</h2>
            <p style="color:rgba(255,255,255,0.4);font-size:0.78rem;margin:0;">
                @if ($code->isFreeAccess())
                    <span style="background:rgba(0,188,212,0.15);color:#00bcd4;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:600;">Free Access</span>
                @else
                    <span style="background:rgba(255,152,0,0.15);color:#ff9800;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:600;">Kode Promo</span>
                @endif
                &middot; Dibuat oleh {{ $code->creator?->name ?? '-' }} &middot; {{ $code->created_at->format('d M Y H:i') }}
            </p>
        </div>
        <div>
            @if ($code->is_active)
                <span style="background:rgba(34,197,94,0.15);color:#22c55e;padding:4px 12px;border-radius:6px;font-size:0.78rem;font-weight:600;">Aktif</span>
            @else
                <span style="background:rgba(239,68,68,0.15);color:#ef4444;padding:4px 12px;border-radius:6px;font-size:0.78rem;font-weight:600;">Nonaktif</span>
            @endif
        </div>
    </div>

    @if ($code->isFreeAccess())
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">TIER</p>
                @if ($code->tier === 'plus_plus')
                    <span style="background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;padding:2px 10px;border-radius:4px;font-size:0.82rem;font-weight:600;">Plus+</span>
                @else
                    <span style="background:rgba(255,255,255,0.1);color:#fff;padding:2px 10px;border-radius:4px;font-size:0.82rem;font-weight:600;">Plus</span>
                @endif
            </div>
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">DURASI</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">{{ $code->duration_days }} hari</p>
            </div>
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">PEMAKAIAN</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">{{ $code->used_count }} / {{ $code->max_uses === 0 ? '∞' : $code->max_uses }}</p>
            </div>
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">EXPIRED AT</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">{{ $code->expires_at?->format('d M Y') ?? 'Tidak ada' }}</p>
            </div>
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">STATUS KODE</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">
                    @if ($code->expires_at && now()->greaterThan($code->expires_at))
                        <span style="color:var(--danger);">Expired</span>
                    @elseif ($code->max_uses > 0 && $code->used_count >= $code->max_uses)
                        <span style="color:var(--danger);">Habis</span>
                    @else
                        <span style="color:#22c55e;">Valid</span>
                    @endif
                </p>
            </div>
        </div>
    @else
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">DISKON</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">
                    {{ $code->discount_type === 'percent' ? "{$code->discount_value}%" : 'Rp'.number_format($code->discount_value, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">TARGET PLAN</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">{{ $code->plan?->name ?? 'Semua plan' }}</p>
            </div>
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">PEMAKAIAN</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">{{ $code->used_count }} / {{ $code->max_uses === 0 ? '∞' : $code->max_uses }}</p>
            </div>
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">POPUP</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">{{ $code->show_popup ? '✅ Ya' : '❌ Tidak' }}</p>
            </div>
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">EXPIRED AT</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">{{ $code->expires_at?->format('d M Y') ?? 'Tidak ada' }}</p>
            </div>
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">STATUS KODE</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">
                    @if ($code->expires_at && now()->greaterThan($code->expires_at))
                        <span style="color:var(--danger);">Expired</span>
                    @elseif ($code->max_uses > 0 && $code->used_count >= $code->max_uses)
                        <span style="color:var(--danger);">Habis</span>
                    @else
                        <span style="color:#22c55e;">Valid</span>
                    @endif
                </p>
            </div>
            @if ($code->popup_title)
            <div>
                <p class="admin-form-label" style="margin:0 0 4px;">POPUP TITLE</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">{{ $code->popup_title }}</p>
            </div>
            @endif
            @if ($code->popup_message)
            <div style="grid-column:span 3;">
                <p class="admin-form-label" style="margin:0 0 4px;">POPUP MESSAGE</p>
                <p style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">{{ $code->popup_message }}</p>
            </div>
            @endif
        </div>
    @endif
</div>

{{-- Daftar Redeemer --}}
<div class="admin-card" style="padding:0;overflow-x:auto;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
        <h3 style="color:#fff;font-size:0.9rem;font-weight:600;margin:0;">Pemakaian Kode</h3>
    </div>
    @if ($code->redeemers->isEmpty())
        <div style="text-align:center;padding:40px 0;color:var(--muted);font-size:0.85rem;">Belum ada yang menggunakan kode ini.</div>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Tanggal Redeem</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($code->redeemers as $redeemer)
                    <tr>
                        <td><strong>{{ $redeemer->name }}</strong></td>
                        <td style="color:var(--muted);font-size:0.8rem;">{{ $redeemer->email }}</td>
                        <td style="color:var(--muted);font-size:0.8rem;">{{ $redeemer->pivot->redeemed_at->format('d M Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
