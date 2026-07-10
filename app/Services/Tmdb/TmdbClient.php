<?php

namespace App\Services\Tmdb;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TmdbClient
{
    private string $apiKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.tmdb.key');
        $this->baseUrl = (string) config('services.tmdb.base_url');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->baseUrl !== '';
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{0: array<string, mixed>, 1: string|null}
     */
    public function get(string $endpoint, array $query = [], string $language = 'id-ID'): array
    {
        if (! $this->isConfigured()) {
            return [[], 'Konfigurasi TMDB belum lengkap. Cek TMDB_API_KEY dan TMDB_BASE_URL di file .env.'];
        }

        $query = array_merge([
            'api_key' => $this->apiKey,
            'language' => $language,
        ], $query);

        try {
            $response = $this->request($endpoint, $query);
        } catch (ConnectionException $exception) {
            report($exception);

            return [[], 'Koneksi ke TMDB sedang bermasalah. Coba lagi sebentar lagi.'];
        }

        if ($response->failed()) {
            Log::warning('TMDB request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            return [[], 'Data film tidak bisa dimuat saat ini.'];
        }

        $data = $response->json();

        if (! is_array($data)) {
            return [[], 'Format data dari TMDB tidak sesuai.'];
        }

        return [$data, null];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{0: array<int, array<string, mixed>>, 1: string|null}
     */
    public function listing(string $endpoint, array $query = [], string $language = 'id-ID'): array
    {
        [$data, $error] = $this->get($endpoint, array_merge(['page' => 1], $query), $language);

        if ($error !== null) {
            return [[], $error];
        }

        $results = $data['results'] ?? [];

        if (! is_array($results)) {
            return [[], 'Format data dari TMDB tidak sesuai.'];
        }

        /** @var array<int, array<string, mixed>> $results */
        return [$results, null];
    }

    /**
     * @return array{body: string, content_type: string}|null
     */
    public function image(string $size, string $path): ?array
    {
        try {
            $response = Http::baseUrl('https://image.tmdb.org/t/p')
                ->connectTimeout(5)
                ->timeout(10)
                ->retry(3, 100, throw: false)
                ->get("/{$size}/{$path}");
        } catch (ConnectionException $exception) {
            report($exception);

            return null;
        }

        if ($response->failed()) {
            Log::warning('TMDB image proxy request failed', [
                'size' => $size,
                'path' => $path,
                'status' => $response->status(),
            ]);

            return null;
        }

        $contentType = (string) $response->header('Content-Type', 'image/jpeg');

        if (! str_starts_with($contentType, 'image/')) {
            return null;
        }

        return [
            'body' => $response->body(),
            'content_type' => $contentType,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function request(string $endpoint, array $query): Response
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(10)
            ->retry(3, 100, throw: false)
            ->get($endpoint, $query);
    }
}
