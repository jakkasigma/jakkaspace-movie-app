<?php

namespace App\Http\Controllers;

use App\Services\Movie\MovieService;
use App\Services\User\SearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $searchService,
        private readonly MovieService $movieService,
    ) {}

    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->value();
        $tab = $request->string('tab', 'films')->value();

        $validTabs = ['films', 'users', 'lists'];
        if (! in_array($tab, $validTabs, true)) {
            $tab = 'films';
        }

        $movies = [];
        $users = null;
        $lists = null;

        if ($query !== '') {
            if ($tab === 'films') {
                $section = $this->movieService->searchMovieSection($query);
                $movies = $section['movies'];
            } elseif ($tab === 'users') {
                $users = $this->searchService->searchUsers($query);
            } elseif ($tab === 'lists') {
                $lists = $this->searchService->searchLists($query);
            }
        }

        return view('search.index', [
            'query' => $query,
            'tab' => $tab,
            'movies' => $movies,
            'users' => $users,
            'lists' => $lists,
        ]);
    }
}
