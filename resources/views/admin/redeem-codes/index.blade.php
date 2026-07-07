@extends('admin.layout')

@section('title', 'Redeem Codes — Admin')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;">
    <div>
        <h1 style="color:#fff;font-size:1.4rem;font-weight:700;margin:0 0 4px;">Redeem Codes</h1>
        <p style="color:rgba(255,255,255,0.45);font-size:0.85rem;margin:0;">Total: {{ $totalCodes }} &middot; Aktif: {{ $totalActive }} &middot; Digunakan: {{ $totalRedeemed }}x</p>
    </div>
</div>

{{-- Buat Kode Baru --}}
<div class="admin-card" style="margin-bottom:28px;">
    <h2 style="color:#fff;font-size:0.9rem;font-weight:600;margin:0 0 14px;">Buat Kode Baru</h2>
    <form method="POST" action="{{ route('admin.redeem-codes.store') }}" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr;gap:10px;align-items:end;">
        @csrf
        <div>
            <label class="admin-form-label" style="margin-bottom:4px;">KODE</label>
            <input type="text" name="code" placeholder="Contoh: BAGIBAGI" required class="admin-form-input">
        </div>
        <div>
            <label class="admin-form-label" style="margin-bottom:4px;">TIER</label>
            <select name="tier" required class="admin-form-input">
                <option value="plus">Plus</option>
                <option value="plus_plus">Plus+</option>
            </select>
        </div>
        <div>
            <label class="admin-form-label" style="margin-bottom:4px;">DURASI</label>
            <select name="duration_days" required class="admin-form-input">
                <option value="30">30 hari</option>
                <option value="365">365 hari</option>
            </select>
        </div>
        <div>
            <label class="admin-form-label" style="margin-bottom:4px;">MAX PAKAI</label>
            <input type="number" name="max_uses" placeholder="0 = unlimited" value="1" min="0" class="admin-form-input">
        </div>
        <button type="submit" class="admin-btn admin-btn-primary" style="border:none;white-space:nowrap;margin-bottom:1px;">Buat Kode</button>
    </form>
</div>

{{-- Daftar Kode --}}
@if ($codes->isEmpty())
    <div style="text-align:center;padding:60px 0;color:rgba(255,255,255,0.3);font-size:0.9rem;">Belum ada kode redeem.</div>
@else
    <div class="admin-card" style="padding:0;overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tier</th>
                    <th>Durasi</th>
                    <th>Pemakaian</th>
                    <th>Status</th>
                    <th>Admin</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($codes as $code)
                    <tr>
                        <td style="font-family:'Courier New',monospace;font-weight:600;letter-spacing:0.5px;">{{ $code->code }}</td>
                        <td>
                            @if ($code->tier === 'plus_plus')
                                <span style="background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:600;">Plus+</span>
                            @else
                                <span style="background:rgba(255,255,255,0.1);color:#fff;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:600;">Plus</span>
                            @endif
                        </td>
                        <td style="color:var(--muted);">{{ $code->duration_days }} hari</td>
                        <td style="color:var(--muted);">{{ $code->used_count }} / {{ $code->max_uses === 0 ? '∞' : $code->max_uses }}</td>
                        <td>
                            @if ($code->is_active)
                                <span style="color:#22c55e;">Aktif</span>
                            @else
                                <span style="color:var(--danger);">Nonaktif</span>
                            @endif
                            @if ($code->expires_at && now()->greaterThan($code->expires_at))
                                <span style="color:var(--danger);margin-left:4px;">(Expired)</span>
                            @endif
                        </td>
                        <td style="color:var(--muted);">{{ $code->creator?->name ?? '-' }}</td>
                        <td style="color:var(--muted);font-size:0.78rem;">{{ $code->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.redeem-codes.show', $code) }}" style="color:var(--muted);text-decoration:none;font-size:0.78rem;margin-right:8px;">Detail</a>
                            @if ($code->is_active)
                                <form method="POST" action="{{ route('admin.redeem-codes.destroy', $code) }}" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:var(--danger);font-size:0.78rem;cursor:pointer;padding:0;" onclick="return confirm('Nonaktifkan kode ini?')">Nonaktifkan</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">
        {{ $codes->links() }}
    </div>
@endif
@endsection
