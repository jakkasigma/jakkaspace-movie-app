@extends('layouts.movie')

@section('title', $list->name . ' — Chat — Jakka Space')
@section('body-class', 'list-chat-room')

@section('body')
    <main class="inbox-chat-page">
        <header class="inbox-chat-header">
            <a href="{{ route('lists.show', $list) }}" class="inbox-chat-back" aria-label="Kembali">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </a>
            <div class="inbox-chat-user">
                <div>
                    <p class="inbox-chat-name">{{ $list->name }}</p>
                    <p style="font-size: 0.73rem; color: rgba(255,255,255,0.35);">👥 {{ $list->approvedMembers()->count() }} anggota</p>
                </div>
            </div>
        </header>

        @include('lists.chat')
    </main>
@endsection
