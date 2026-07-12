@extends('layouts.movie')

@section('title', 'Riwayat Langganan — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <a href="{{ route('plus') }}" class="profile-back-link">← Kembali ke Plus</a>

        <header class="space-header">
            <div class="space-header-inner">
                <h1 class="space-page-title">RIWAYAT LANGGANAN</h1>
                <p class="space-page-subtitle">Catatan semua transaksi subscription kamu.</p>
            </div>
        </header>

        <div class="space-body">
            @if ($transactions->isEmpty())
                <x-space.empty icon="list" message="Belum ada riwayat langganan." />
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.83rem;">
                        <thead>
                            <tr style="border-bottom:1px solid rgba(255,255,255,0.08);">
                                <th style="padding:10px 12px;text-align:left;color:var(--muted);font-weight:600;">Tgl</th>
                                <th style="padding:10px 12px;text-align:left;color:var(--muted);font-weight:600;">Aksi</th>
                                <th style="padding:10px 12px;text-align:left;color:var(--muted);font-weight:600;">Paket</th>
                                <th style="padding:10px 12px;text-align:left;color:var(--muted);font-weight:600;">Harga</th>
                                <th style="padding:10px 12px;text-align:left;color:var(--muted);font-weight:600;">Metode</th>
                                <th style="padding:10px 12px;text-align:left;color:var(--muted);font-weight:600;">Aktif Sampai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $tx)
                                @php
                                    $actionLabels = [
                                        'subscribe' => ['Langganan Baru', '#4caf50'],
                                        'renew' => ['Perpanjangan', '#2196f3'],
                                        'upgrade' => ['Upgrade', '#ff9800'],
                                        'cancel' => ['Dibatalkan', '#f44336'],
                                        'admin_grant' => ['Grant Admin', '#9c27b0'],
                                        'admin_extend' => ['Extend Admin', '#9c27b0'],
                                        'redeem' => ['Redeem', '#00bcd4'],
                                    ];
                                    [$actionLabel, $actionColor] = $actionLabels[$tx->action] ?? [$tx->action, 'var(--muted)'];
                                    $tierLabel = $tx->tier === 'plus_plus' ? 'Plus+' : ($tx->tier === 'plus' ? 'Plus' : $tx->tier);
                                @endphp
                                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                                    <td style="padding:10px 12px;color:var(--muted);white-space:nowrap;">{{ $tx->created_at->format('d M Y H:i') }}</td>
                                    <td style="padding:10px 12px;">
                                        <span style="color:{{ $actionColor }};font-weight:600;">{{ $actionLabel }}</span>
                                    </td>
                                    <td style="padding:10px 12px;">
                                        <strong>{{ $tierLabel }}</strong>
                                        @if ($tx->plan)
                                            <br><span style="font-size:0.75rem;color:var(--muted);">{{ $tx->plan->name }}</span>
                                        @endif
                                    </td>
                                    <td style="padding:10px 12px;">
                                        @if ($tx->price > 0)
                                            Rp{{ number_format($tx->price, 0, ',', '.') }}
                                        @else
                                            <span style="color:var(--muted);">Gratis</span>
                                        @endif
                                    </td>
                                    <td style="padding:10px 12px;color:var(--muted);text-transform:capitalize;">
                                        {{ $tx->payment_method ?? '-' }}
                                        @if ($tx->promo_code)
                                            <br><span style="font-size:0.72rem;color:var(--accent);">Promo: {{ $tx->promo_code }}</span>
                                        @endif
                                    </td>
                                    <td style="padding:10px 12px;color:var(--muted);white-space:nowrap;">
                                        {{ $tx->expires_at?->format('d M Y') ?? '-' }}
                                    </td>
                                </tr>
                                @if ($tx->notes)
                                    <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                                        <td colspan="6" style="padding:0 12px 8px;font-size:0.75rem;color:var(--muted);font-style:italic;">
                                            {{ $tx->notes }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($transactions->hasPages())
                    <div style="margin-top:20px;">{{ $transactions->links() }}</div>
                @endif
            @endif
        </div>
    </main>

    @endsection
