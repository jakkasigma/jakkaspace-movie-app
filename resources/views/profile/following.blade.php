@extends('layouts.movie')

@section('title', 'Following — ' . ($profile->name ?? $profile->username))
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <a href="{{ route('profile.show', $profile->username) }}" class="profile-back-link">← {{ $profile->name }}</a>
                    <h1 class="space-page-title">FOLLOWING</h1>
                </div>
            </div>
        </header>

        <div class="space-body">
            @if ($users->isEmpty())
                <div class="space-empty">Belum mengikuti siapapun.</div>
            @else
                <div class="user-list">
                    @foreach ($users as $user)
                        <div class="user-list-row">
                            <x-user-avatar :user="$user" class="user-list-avatar" placeholder-class="user-list-avatar user-list-avatar-placeholder" />
                            <div class="user-list-info">
                                @if ($user->username)
                                    <a href="{{ route('profile.show', $user->username) }}" class="user-list-name">{{ $user->name }}</a>
                                    <span class="user-list-username">{{ '@' . $user->username }}</span>
                                @else
                                    <span class="user-list-name">{{ $user->name }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    @endsection
