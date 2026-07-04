@extends('layouts.movie')

@section('title', 'Notifikasi — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">NOTIFIKASI</h1>
                    <p class="space-page-subtitle">Aktivitas terbaru yang berkaitan denganmu.</p>
                </div>
                @if ($notifications->isNotEmpty())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="notif-mark-all-btn">Tandai semua dibaca</button>
                    </form>
                @endif
            </div>
        </header>

        <div class="space-body">
            @if ($notifications->isEmpty())
                <div class="space-empty">Belum ada notifikasi.</div>
            @else
                <div class="notif-list">
                    @foreach ($notifications as $notif)
                        @php
                            $data = $notif->data;
                            $isUnread = $notif->read_at === null;
                        @endphp

                        <article class="notif-item {{ $isUnread ? 'notif-unread' : '' }}">
                            {{-- Actor avatar --}}
                            <div class="notif-avatar-wrap">
                                @if (! empty($data['actor_avatar']))
                                    <img src="{{ $data['actor_avatar'] }}" alt="{{ $data['actor_name'] }}" class="notif-avatar">
                                @else
                                    <div class="notif-avatar notif-avatar-placeholder">
                                        {{ strtoupper(substr($data['actor_name'] ?? '?', 0, 1)) }}
                                    </div>
                                @endif

                                {{-- Type icon --}}
                                <span class="notif-type-icon">
                                    @if ($data['type'] === 'follow') 👤
                                    @elseif ($data['type'] === 'review_like') ♥
                                    @elseif ($data['type'] === 'diary_like') ♥
                                    @elseif ($data['type'] === 'review_comment') 💬
                                    @endif
                                </span>
                            </div>

                            {{-- Text --}}
                            <div class="notif-body">
                                <p class="notif-text">
                                    @if (! empty($data['actor_username']))
                                        <a href="{{ route('profile.show', $data['actor_username']) }}" class="notif-actor">{{ $data['actor_name'] }}</a>
                                    @else
                                        <strong class="notif-actor">{{ $data['actor_name'] }}</strong>
                                    @endif

                                    @if ($data['type'] === 'follow')
                                        mulai mengikutimu.
                                    @elseif ($data['type'] === 'review_like')
                                        menyukai reviewmu.
                                        @if (! empty($data['tmdb_id']))
                                            <a href="{{ route('reviews.show', $data['review_id']) }}" class="notif-link">Lihat review →</a>
                                        @endif
                                    @elseif ($data['type'] === 'diary_like')
                                        menyukai diary entry-mu.
                                    @elseif ($data['type'] === 'review_comment')
                                        berkomentar di reviewmu:
                                        @if (! empty($data['comment_preview']))
                                            <span class="notif-comment-preview">"{{ $data['comment_preview'] }}"</span>
                                        @endif
                                        <a href="{{ route('reviews.show', $data['review_id']) }}" class="notif-link">Lihat →</a>
                                    @endif
                                </p>
                                <span class="notif-time">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>

                            @if ($isUnread)
                                <span class="notif-dot" aria-label="Belum dibaca"></span>
                            @endif
                        </article>
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
