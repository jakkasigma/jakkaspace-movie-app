<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        // Discover digabung ke home — teruskan query params kalau ada
        $params = array_filter([
            'genre' => $request->integer('genre') ?: null,
            'year' => $request->integer('year') ?: null,
            'sort_by' => $request->string('sort_by')->value() ?: null,
            'page' => $request->integer('page') > 1 ? $request->integer('page') : null,
        ]);

        return redirect()->route('movies.index', $params);
    }
}
