@extends('layouts.movie')

@section('title', $genreName . ' — Jakka Space')
@section('description', 'Film ' . $genreName . ' populer pilihan dari TMDB.')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="discover-page">
        <a href="{{ route('movies.discover') }}" class="profile-back-link" style="margin-left:24px;">← Discover</a>
        <header class="discover-header">
            <div class="discover-header-copy">
                <p class="discover-eyebrow">Genre</p>
                <h1 class="discover-title">{{ strtoupper($genreName) }}</h1>
                <p class="discover-subtitle">Film {{ $genreName }} populer dari TMDB.</p>
            </div>
        </header>

        <div class="discover-body">
            @if (empty($movies))
                <div class="empty-state">Belum ada film untuk genre ini.</div>
            @else
                <div class="movie-grid">
                    @foreach ($movies as $movie)
                        <x-movie.card :movie="$movie" :rank="$loop->iteration" />
                    @endforeach
                </div>

                <x-movie.pagination :currentPage="$currentPage" :totalPages="$totalPages" />
            @endif
        </div>
    </main>

    @endsection
