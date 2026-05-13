<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MovieController extends Controller
{
    /**
     * Menampilkan daftar film populer dari TMDB.
     */
    public function index()
    {
        // Mengambil data konfigurasi dari config/services.php
        $apiKey = config('services.tmdb.key');
        $baseUrl = config('services.tmdb.base_url');

        // Melakukan request ke API TMDB
        $response = Http::get("{$baseUrl}/movie/popular", [
            'api_key' => $apiKey,
            'language' => 'id-ID' // Menggunakan bahasa Indonesia
        ]);

        // Mengambil hasil dalam bentuk array
        $movies = $response->json()['results'];

        // Mengirim data ke file resources/views/welcome.blade.php
        return view('welcome', compact('movies'));
    }
}