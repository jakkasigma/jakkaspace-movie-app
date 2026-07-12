@extends('layouts.movie')

@section('title', 'Inbox — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="inbox-page">

        <header class="inbox-header">
            <div class="inbox-header-inner">
                <h1 class="inbox-title">INBOX</h1>
            </div>
        </header>

        <div class="inbox-body">

            @if (session('error'))
                <div class="inbox-alert inbox-alert--error">{{ session('error') }}</div>
            @endif

            {{-- Existing conversations --}}
            @if ($conversations->isNotEmpty())
                <div class="inbox-list-label">Percakapan</div>
                <div class="inbox-list">
                    @foreach ($conversations as $conversation)
                        @php
                            $other = $conversation->members->first();
                            $lastMsg = $conversation->messages->first();
                        @endphp
                        <a href="{{ route('inbox.show', $conversation) }}" class="inbox-conv-item {{ $other?->isPlus() ? 'item-premium' : '' }}"
                           @if ($other?->isPlus() && $other->theme) style="--item-accent: {{ $other->theme->accent_color }}" @endif>
                             <div class="inbox-conv-avatar">
                                <x-user-avatar :user="$other" class="inbox-avatar-img" placeholder-class="inbox-avatar-img inbox-avatar-placeholder" />
                            </div>
                            <div class="inbox-conv-info">
                                <p class="inbox-conv-name">{{ $other?->name ?? 'Pengguna' }}</p>
                                @if ($other?->username)
                                    <span class="inbox-conv-handle">{{ '@' . $other->username }}</span>
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
                                @if ($conversation->unread_count > 0)
                                    <span class="inbox-unread-badge">{{ $conversation->unread_count > 9 ? '9+' : $conversation->unread_count }}</span>
                                @endif
                                @if ($lastMsg)
                                    <span class="inbox-conv-time">{{ $lastMsg->created_at->diffForHumans(short: true) }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Following / Kontak --}}
            <div class="inbox-list-label">Kontak</div>
            <div class="inbox-contacts">
                @forelse ($following as $contact)
                    @php
                        $existingConv = $conversations->first(fn ($c) =>
                            $c->members->first()?->id === $contact->id
                        );
                    @endphp
                    <a href="{{ $existingConv ? route('inbox.show', $existingConv) : route('inbox.direct', $contact) }}"
                       class="inbox-contact-item {{ $contact->isPlus() ? 'item-premium' : '' }}"
                       @if (! $existingConv) data-new-chat="1" @endif
                       @if ($contact->isPlus() && $contact->theme) style="--item-accent: {{ $contact->theme->accent_color }}" @endif>
                        <div class="inbox-contact-avatar">
                            <x-user-avatar :user="$contact" class="inbox-avatar-img" placeholder-class="inbox-avatar-img inbox-avatar-placeholder" />
                        </div>
                        <div class="inbox-contact-info">
                            <p class="inbox-contact-name">{{ $contact->name }}</p>
                            @if ($contact->username)
                                <span class="inbox-contact-handle">{{ '@' . $contact->username }}</span>
                            @endif
                        </div>
                        @if ($existingConv)
                            <span class="inbox-contact-status">Pesan</span>
                        @else
                            <span class="inbox-contact-status inbox-contact-status-new">Mulai</span>
                        @endif
                    </a>
                @empty
                    <div class="inbox-empty">
                        <svg class="inbox-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <p class="inbox-empty-title">Belum ada kontak</p>
                        <p class="inbox-empty-hint">
                            Ikuti pengguna lain untuk memulai percakapan.
                        </p>
                        <a href="{{ route('search', ['tab' => 'users']) }}" class="inbox-empty-cta">Cari Pengguna</a>
                    </div>
                @endforelse
            </div>

        </div>
    </main>

    @endsection
