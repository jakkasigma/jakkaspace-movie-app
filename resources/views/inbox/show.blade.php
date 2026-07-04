@extends('layouts.movie')

@php
    $other = $conversation->members->filter(fn ($m) => $m->id !== auth()->id())->first();
    $title = $other?->name ?? 'Pesan';
@endphp

@section('title', $title . ' — Inbox — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="inbox-page inbox-chat-page">

        {{-- Chat header --}}
        <header class="inbox-chat-header">
            <a href="{{ route('inbox') }}" class="inbox-chat-back" aria-label="Kembali ke inbox">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </a>
            <div class="inbox-chat-user">
                @if ($other?->avatar_url)
                    <img src="{{ $other->avatar_url }}" alt="{{ $other->name }}" class="inbox-chat-avatar">
                @else
                    <div class="inbox-chat-avatar inbox-avatar-placeholder">
                        {{ strtoupper(substr($other?->name ?? '?', 0, 1)) }}
                    </div>
                @endif
                <div>
                    <p class="inbox-chat-name">{{ $other?->name ?? 'Pengguna' }}</p>
                    @if ($other?->username)
                        <a href="{{ route('profile.show', $other->username) }}" class="inbox-chat-handle">@{{ $other->username }}</a>
                    @endif
                </div>
            </div>
        </header>

        {{-- Messages --}}
        <div class="inbox-messages" id="inbox-messages">
            @if ($messages->isEmpty())
                <div class="inbox-messages-empty">
                    Mulai percakapan dengan mengirim pesan pertamamu.
                </div>
            @else
                @foreach ($messages as $message)
                    @php $isMine = $message->user_id === auth()->id(); @endphp
                    <div class="inbox-msg {{ $isMine ? 'inbox-msg--mine' : 'inbox-msg--theirs' }}">
                        @if (! $isMine)
                            <div class="inbox-msg-avatar">
                                @if ($message->sender?->avatar_url)
                                    <img src="{{ $message->sender->avatar_url }}" alt="{{ $message->sender->name }}" class="inbox-mini-avatar">
                                @else
                                    <div class="inbox-mini-avatar inbox-avatar-placeholder">
                                        {{ strtoupper(substr($message->sender?->name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="inbox-msg-bubble">
                            @if ($message->type === 'film_share' && $message->tmdb_id)
                                @php $film = $movieCache[$message->tmdb_id] ?? null; @endphp
                                <a href="{{ route('movies.show', $message->tmdb_id) }}" class="inbox-film-share">
                                    @if ($film && ($film['poster_url'] ?? null))
                                        <img src="{{ $film['poster_url'] }}" alt="{{ $film['title'] ?? '' }}" class="inbox-film-poster">
                                    @else
                                        <div class="inbox-film-poster inbox-film-no-poster">🎬</div>
                                    @endif
                                    <div class="inbox-film-info">
                                        <span class="inbox-film-kicker">Berbagi Film</span>
                                        <p class="inbox-film-title">{{ $film['title'] ?? 'Film #' . $message->tmdb_id }}</p>
                                        @if ($film['release_year'] ?? null)
                                            <span class="inbox-film-year">{{ $film['release_year'] }}</span>
                                        @endif
                                    </div>
                                </a>
                            @else
                                <p class="inbox-msg-text">{{ $message->body }}</p>
                            @endif
                            <span class="inbox-msg-time">{{ $message->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Input area --}}
        <div class="inbox-input-wrap">
            <form method="POST" action="{{ route('inbox.messages.store', $conversation) }}" class="inbox-input-form">
                @csrf
                <input type="hidden" name="type" value="text">
                <textarea
                    name="body"
                    class="inbox-textarea"
                    placeholder="Tulis pesan..."
                    rows="1"
                    maxlength="2000"
                    aria-label="Pesan"
                    required
                ></textarea>
                <button type="submit" class="inbox-send-btn" aria-label="Kirim">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </form>
        </div>

    </main>
@endsection

@push('head')
<script>
    // Auto-scroll to bottom on load
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('inbox-messages');
        if (el) { el.scrollTop = el.scrollHeight; }

        // Auto-resize textarea
        const ta = document.querySelector('.inbox-textarea');
        if (ta) {
            ta.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Ctrl+Enter or Cmd+Enter to submit
            ta.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    this.closest('form').submit();
                }
            });
        }
    });
</script>
@endpush
