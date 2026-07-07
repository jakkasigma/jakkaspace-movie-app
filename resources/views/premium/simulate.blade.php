@extends('layouts.movie')

@section('title', 'Simulasi Pembayaran — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="simulate-page">
        <div class="simulate-card">
            <div class="simulate-header">
                <span class="simulate-check">✓</span>
                <h1 class="simulate-title">Pembayaran Berhasil!</h1>
            </div>

            <div class="simulate-details">
                <div class="simulate-row">
                    <span class="simulate-label">Paket</span>
                    <span class="simulate-value">{{ $plan?->name ?? 'Plus Bulanan' }}</span>
                </div>
                @if ($promoDiscount)
                    <div class="simulate-row">
                        <span class="simulate-label">Promo</span>
                        <span class="simulate-value" style="color:#4caf50;">{{ $promoDiscount['label'] }} ({{ $promoDiscount['name'] }})</span>
                    </div>
                    <div class="simulate-row">
                        <span class="simulate-label">Harga Asli</span>
                        <span class="simulate-value" style="text-decoration:line-through;color:var(--muted);">Rp{{ number_format($promoDiscount['original'], 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="simulate-row">
                    <span class="simulate-label">Total</span>
                    <span class="simulate-value simulate-price">Rp{{ number_format($price, 0, ',', '.') }}</span>
                </div>
                <div class="simulate-row">
                    <span class="simulate-label">Metode</span>
                    <span class="simulate-value">{{ $methodLabel }}</span>
                </div>
            </div>

            <p class="simulate-note">Ini adalah simulasi pembayaran. Di production nanti akan diproses via Midtrans.</p>

            <div class="simulate-actions">
                <a href="{{ route('plus') }}" class="simulate-btn">Pilih Tema</a>
                <a href="{{ route('your-space') }}" class="simulate-btn simulate-btn-secondary">Ke Your Space</a>
            </div>
        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
