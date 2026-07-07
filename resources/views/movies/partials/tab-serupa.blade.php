<div class="detail-extra detail-tab-serupa">
    @if (! empty($similarMovies))
        <section class="detail-similar-section" aria-labelledby="detail-similar-title">
            <h3 id="detail-similar-title" class="detail-section-label">Film Serupa</h3>
            <div class="movie-row">
                @foreach ($similarMovies as $similarMovie)
                    <x-movie.card :movie="$similarMovie" />
                @endforeach
            </div>
        </section>
    @else
        <p class="detail-synopsis" style="padding-top:8px;">Tidak ada film serupa ditemukan.</p>
    @endif

    @if (! empty($genreRecommendations))
        <section class="detail-similar-section" aria-labelledby="detail-genre-rec-title">
            <h3 id="detail-genre-rec-title" class="detail-section-label">Karena kamu menonton film ini</h3>
            <div class="movie-row">
                @foreach ($genreRecommendations as $recMovie)
                    <x-movie.card :movie="$recMovie" />
                @endforeach
            </div>
        </section>
    @endif
</div>
