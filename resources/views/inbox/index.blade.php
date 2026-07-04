@extends('layouts.movie')

@section('title', 'Inbox — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="inbox-page">

        <header class="inbox-header">
            <div class="inbox-header-inner">
                <h1 class="inbox-title">INBOX</h1>
                <p class="inbox-subtitle">Pesan langsung dengan sesama pengguna.</p>
            </div>
        </header>

        <div class="inbox-body">

            @if (session('error'))
                <div class="inbox-alert inbox-alert--error">{{ session('error') }}</div>
            @endif

            @if ($conversations->isEmpty())
                <div class="inbox-empty">
                    <svg class="inbox-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <p class="inbox-empty-title">Belum ada pesan</p>
                    <p class="inbox-empty-hint">
                        Cari pengguna dan mulai percakapan dari halaman profil mereka.
                    </p>
                    <a href="{{ route('search', ['tab' => 'users']) }}" class="inbox-empty-cta">Cari Pengguna</a>
                </div>
            @else
                <div class="inbox-list">
                    @foreach ($conversations as $conversation)
                        @php
                            $other = $conversation->members->first();
                            $lastMsg = $conversation->messages->first();
                        @endphp
                        <a href="{{ route('inbox.show', $conversation) }}" class="inbox-conv-item">
                            <div class="inbox-conv-avatar">
                                @if ($other?->avatar_url)
                                    <img src="{{ $other->avatar_url }}" alt="{{ $other->name }}" class="inbox-avatar-img">
                                @else
                                    <div class="inbox-avatar-img inbox-avatar-placeholder">
                                        {{ strtoupper(substr($other?->name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="inbox-conv-info">
                                <p class="inbox-conv-name">{{ $other?->name ?? 'Pengguna' }}</p>
                                @if ($other?->username)
                                    <span class="inbox-conv-handle">@{{ $other->username }}</span>
                                @endif
                                @if ($lastMsg)
                                    <p class="inbox-conv-preview">
                                        @if ($lastMsg->type === 'film_share')
                                            🎬 Berbagi film
                                        @else
                                            {{ Str::limit($lastMsg->body ?? '', 50) }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <div class="inbox-conv-meta">
                                @if ($lastMsg)
                                    <span class="inbox-conv-time">{{ $lastMsg->created_at->diffForHumans(short: true) }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
