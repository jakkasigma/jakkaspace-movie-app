@extends('layouts.movie')

@section('title', 'Favorit — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <a href="{{ route('your-space') }}" class="profile-back-link">← Your Space</a>
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">FAVORIT</h1>
                    <p class="space-page-subtitle">Film-film yang paling kamu suka.</p>
                </div>
                <div class="space-header-stats">
                    <span class="space-header-stat">{{ $favoritesInfo['count'] }} film</span>
                </div>
            </div>
        </header>

        <x-space.nav active="favorites" />
        <x-space.tab-bar active="favorites" />

        <div class="space-body">
            @if (empty($movies))
                <x-space.empty icon="heart" message="Belum ada favorit." />
            @else
                <div class="movie-grid">
                    @foreach ($movies as $movie)
                        <x-movie.card :movie="$movie" :rank="$loop->iteration" />
                    @endforeach
                </div>
            @endif
        </div>
    </main>

    @endsection
