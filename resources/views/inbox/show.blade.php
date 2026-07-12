@extends('layouts.movie')

@php
    $other = $conversation->members->filter(fn ($m) => $m->id !== auth()->id())->first();
    $title = $other?->name ?? 'Pesan';
@endphp

@section('title', $title . ' — Inbox — Jakka Space')
@section('body-class', 'inbox-chat-room')

@section('body')
    <main class="inbox-chat-page">

        {{-- Chat header --}}
        <header class="inbox-chat-header">
            <a href="{{ route('inbox') }}" class="inbox-chat-back" aria-label="Kembali ke inbox">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </a>
            <div class="inbox-chat-user">
                <x-user-avatar :user="$other" class="inbox-chat-avatar" placeholder-class="inbox-chat-avatar inbox-avatar-placeholder" />
                <div>
                    <p class="inbox-chat-name">{{ $other?->name ?? 'Pengguna' }}</p>
                    @if ($other?->username)
                        <a href="{{ route('profile.show', $other->username) }}" class="inbox-chat-handle">{{ '@' . $other->username }}</a>
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
                @php $lastDate = null; @endphp
                @foreach ($messages as $message)
                    @php
                        $isMine = $message->user_id === auth()->id();
                        $msgDate = $message->created_at->format('Y-m-d');
                    @endphp

                    {{-- Date separator --}}
                    @if ($msgDate !== $lastDate)
                        @php $lastDate = $msgDate; @endphp
                        <div class="inbox-date-sep">
                            <span data-utc="{{ $message->created_at->toIso8601String() }}" data-fmt="date-sep">{{ $message->created_at->format('d/m/Y') }}</span>
                        </div>
                    @endif

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

                        <div class="inbox-msg-bubble {{ ($message->sender?->isPlus() && $message->sender->theme) ? 'inbox-msg-premium' : '' }}" @if ($message->sender?->isPlus() && $message->sender->theme) style="--accent-color: {{ $message->sender->theme->accent_color }};" @endif>
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
                            <span class="inbox-msg-time" data-utc="{{ $message->created_at->toIso8601String() }}" data-fmt="time">{{ $message->created_at->format('H:i') }}</span>
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
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('inbox-messages');
        const shouldScroll = el && (el.scrollTop + el.clientHeight >= el.scrollHeight - 60);
        if (el && shouldScroll) { el.scrollTop = el.scrollHeight; }

        const ta = document.querySelector('.inbox-textarea');
        if (ta) {
            ta.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            ta.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    this.closest('form').submit();
                }
            });
        }

        // Real-time listening via Echo
        if (window.Echo) {
            const conversationId = {{ $conversation->id }};
            const userId = {{ auth()->id() }};

            window.Echo.private('chat.' + conversationId)
                .listen('MessageSent', function (e) {
                    if (e.message.user_id === userId) return;

                    const container = document.getElementById('inbox-messages');
                    if (!container) return;

                    const empty = container.querySelector('.inbox-messages-empty');
                    if (empty) empty.remove();

                    const msg = e.message;
                    const isMine = msg.user_id === userId;
                    const utcStr = msg.created_at;

                    const lastSep = container.querySelector('.inbox-date-sep:last-child');
                    const sepDate = new Date(utcStr);
                    const today = new Date();
                    const sepKey = today.toDateString();

                    if (!lastSep || lastSep.dataset.date !== sepKey) {
                        const sep = document.createElement('div');
                        sep.className = 'inbox-date-sep';
                        sep.dataset.date = sepKey;
                        const span = document.createElement('span');
                        span.setAttribute('data-utc', utcStr);
                        span.setAttribute('data-fmt', 'date-sep');
                        span.textContent = 'Hari Ini';
                        sep.appendChild(span);
                        container.appendChild(sep);
                    }

                    const div = document.createElement('div');
                    div.className = 'inbox-msg ' + (isMine ? 'inbox-msg--mine' : 'inbox-msg--theirs');

                    const avatarHtml = !isMine ? '<div class="inbox-msg-avatar">' +
                        (msg.sender.avatar_url
                            ? '<img src="' + msg.sender.avatar_url + '" alt="' + msg.sender.name + '" class="inbox-mini-avatar">'
                            : '<div class="inbox-mini-avatar inbox-avatar-placeholder">' + (msg.sender.name ? msg.sender.name.charAt(0).toUpperCase() : '?') + '</div>'
                        ) + '</div>' : '';

                    const textHtml = msg.type === 'film_share'
                        ? '<div class="inbox-film-share" style="padding:12px;color:rgba(255,255,255,0.5);font-size:0.8rem">🎬 Film #' + msg.tmdb_id + '</div>'
                        : '<p class="inbox-msg-text">' + (msg.body || '') + '</p>';

                    div.innerHTML = avatarHtml
                        + '<div class="inbox-msg-bubble">'
                        + textHtml
                        + '<span class="inbox-msg-time" data-utc="' + utcStr + '" data-fmt="time"></span>'
                        + '</div>';

                    container.appendChild(div);
                    container.scrollTop = container.scrollHeight;

                    window.dispatchEvent(new CustomEvent('timestamps-updated'));
                });
        }
    });
</script>
@endpush
