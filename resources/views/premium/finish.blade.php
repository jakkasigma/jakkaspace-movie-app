@extends('layouts.movie')

@section('title', 'Hasil Pembayaran — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page" style="min-height:100vh;display:flex;align-items:center;justify-content:center;">
        <div style="text-align:center;max-width:400px;padding:40px 24px;">
            @if ($status === 'success')
                <div style="font-size:3rem;margin-bottom:16px;">🎉</div>
                <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:#fff;text-transform:uppercase;">Pembayaran Berhasil</h1>
                <p style="color:rgba(255,255,255,0.7);font-size:0.9rem;margin:12px 0 24px;">{{ $message }}</p>
                <a href="{{ route('plus') }}" class="plus-cta-btn" style="display:inline-block;text-decoration:none;">→ Kembali ke Plus</a>
            @elseif ($status === 'pending')
                <div style="font-size:3rem;margin-bottom:16px;">⏳</div>
                <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:#fff;text-transform:uppercase;">Menunggu Pembayaran</h1>
                <p style="color:rgba(255,255,255,0.7);font-size:0.9rem;margin:12px 0 24px;">{{ $message }}</p>
                <a href="{{ route('plus.history') }}" class="plus-cta-btn" style="display:inline-block;text-decoration:none;">→ Cek Status</a>
            @else
                <div style="font-size:3rem;margin-bottom:16px;">❌</div>
                <h1 style="font-family:'Bebas Neue',sans-serif;font-size:2rem;color:#fff;text-transform:uppercase;">Pembayaran Gagal</h1>
                <p style="color:rgba(255,255,255,0.7);font-size:0.9rem;margin:12px 0 24px;">{{ $message }}</p>
                <a href="{{ route('plus') }}" class="plus-cta-btn" style="display:inline-block;text-decoration:none;">→ Coba Lagi</a>
            @endif
        </div>
    </main>

    @endsection
