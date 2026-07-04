<?php

namespace App\Http\Controllers;

use App\Services\Movie\MovieService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    public function show(int $genre, Request $request): View
    {
        $page = max(1, $request->integer('page', 1));
        $result = $this->movieService->moviesByGenre($genre, $page);

        return view('movies.genre', [
            'movies' => $result['movies'],
            'currentPage' => $result['current_page'],
            'totalPages' => $result['total_pages'],
            'genreId' => $genre,
            'genreName' => $result['genre_name'],
        ]);
    }
}
