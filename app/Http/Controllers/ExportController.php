<?php

namespace App\Http\Controllers;

use App\Models\DiaryEntry;
use App\Models\Review;
use App\Models\WatchHistory;
use App\Services\Movie\MovieService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    public function export(Request $request, string $type): Response|StreamedResponse
    {
        $user = $request->user();

        abort_unless($user->isPlus(), 403);

        return match ($type) {
            'diary' => $this->exportDiary($user),
            'reviews' => $this->exportReviews($user),
            'history' => $this->exportHistory($user),
            'all' => $this->exportAll($user),
            default => abort(404),
        };
    }

    private function movieTitle(int $tmdbId): array
    {
        [$movie] = $this->movieService->findMovie($tmdbId);

        return [
            'title' => $movie['title'] ?? 'Film #'.$tmdbId,
            'poster_url' => $movie['poster_url'] ?? '',
            'director' => $movie['director'] ?? '',
            'genres' => $movie['genres'] ?? '',
            'release_year' => $movie['release_year'] ?? '',
        ];
    }

    private function exportDiary(mixed $user): StreamedResponse
    {
        $entries = DiaryEntry::where('user_id', $user->id)
            ->orderByDesc('watched_at')
            ->get();

        return response()->streamDownload(function () use ($entries): void {
            $fh = fopen('php://output', 'wb');
            fputcsv($fh, ['Judul Film', 'Tanggal Tayang', 'Rating/5', 'Review', 'Diary Entry', 'Mood', 'Sutradara', 'Genre', 'Poster URL']);

            foreach ($entries as $entry) {
                $movie = $this->movieTitle($entry->tmdb_id);
                $review = Review::where('user_id', $entry->user_id)
                    ->where('tmdb_id', $entry->tmdb_id)
                    ->first();

                fputcsv($fh, [
                    $movie['title'],
                    $entry->watched_at?->format('Y-m-d') ?? '',
                    $review?->rating ?? '',
                    strip_tags((string) $review?->body ?? ''),
                    $entry->notes ?? '',
                    $entry->mood ?? '',
                    $movie['director'],
                    $movie['genres'],
                    $movie['poster_url'],
                ]);
            }

            fclose($fh);
        }, 'diary.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function exportReviews(mixed $user): StreamedResponse
    {
        $reviews = Review::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->streamDownload(function () use ($reviews): void {
            $fh = fopen('php://output', 'wb');
            fputcsv($fh, ['Judul Film', 'Tanggal Review', 'Rating/5', 'Review Body', 'Spoiler', 'Sutradara', 'Genre', 'Poster URL']);

            foreach ($reviews as $review) {
                $movie = $this->movieTitle($review->tmdb_id);

                fputcsv($fh, [
                    $movie['title'],
                    $review->created_at->format('Y-m-d'),
                    $review->rating ?? '',
                    strip_tags((string) $review->body),
                    $review->has_spoiler ? 'Ya' : 'Tidak',
                    $movie['director'],
                    $movie['genres'],
                    $movie['poster_url'],
                ]);
            }

            fclose($fh);
        }, 'reviews.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function exportHistory(mixed $user): StreamedResponse
    {
        $history = WatchHistory::where('user_id', $user->id)
            ->orderByDesc('watched_at')
            ->get();

        return response()->streamDownload(function () use ($history): void {
            $fh = fopen('php://output', 'wb');
            fputcsv($fh, ['Judul Film', 'Tanggal Tonton', 'Status', 'Sutradara', 'Genre', 'Poster URL']);

            foreach ($history as $entry) {
                $movie = $this->movieTitle($entry->tmdb_id);
                $statusLabels = ['watched' => 'Ditonton', 'plan_to_watch' => 'Akan Ditonton', 'watching' => 'Sedang Ditonton'];

                fputcsv($fh, [
                    $movie['title'],
                    $entry->watched_at?->format('Y-m-d') ?? '',
                    $statusLabels[$entry->status] ?? $entry->status,
                    $movie['director'],
                    $movie['genres'],
                    $movie['poster_url'],
                ]);
            }

            fclose($fh);
        }, 'history.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function exportAll(mixed $user): StreamedResponse
    {
        return response()->streamDownload(function () use ($user): void {
            $zip = new \ZipArchive;
            $tmp = tempnam(sys_get_temp_dir(), 'export_');

            if ($zip->open($tmp, \ZipArchive::CREATE) !== true) {
                return;
            }

            $zip->addFromString('diary.csv', $this->csvString(fn ($fh) => $this->writeDiaryCsv($fh, $user)));
            $zip->addFromString('reviews.csv', $this->csvString(fn ($fh) => $this->writeReviewsCsv($fh, $user)));
            $zip->addFromString('history.csv', $this->csvString(fn ($fh) => $this->writeHistoryCsv($fh, $user)));

            $zip->close();

            readfile($tmp);
            unlink($tmp);
        }, 'jakkaspace-export.zip', ['Content-Type' => 'application/zip']);
    }

    private function csvString(callable $writer): string
    {
        ob_start();
        $fh = fopen('php://output', 'wb');
        $writer($fh);
        fclose($fh);

        return ob_get_clean();
    }

    private function writeDiaryCsv($fh, mixed $user): void
    {
        fputcsv($fh, ['Judul Film', 'Tanggal Tayang', 'Rating/5', 'Review', 'Diary Entry', 'Mood', 'Sutradara', 'Genre', 'Poster URL']);
        $entries = DiaryEntry::where('user_id', $user->id)->orderByDesc('watched_at')->get();

        foreach ($entries as $entry) {
            $movie = $this->movieTitle($entry->tmdb_id);
            $review = Review::where('user_id', $entry->user_id)->where('tmdb_id', $entry->tmdb_id)->first();

            fputcsv($fh, [
                $movie['title'], $entry->watched_at?->format('Y-m-d') ?? '',
                $review?->rating ?? '', strip_tags((string) $review?->body ?? ''),
                $entry->notes ?? '', $entry->mood ?? '',
                $movie['director'], $movie['genres'], $movie['poster_url'],
            ]);
        }
    }

    private function writeReviewsCsv($fh, mixed $user): void
    {
        fputcsv($fh, ['Judul Film', 'Tanggal Review', 'Rating/5', 'Review Body', 'Spoiler', 'Sutradara', 'Genre', 'Poster URL']);
        $reviews = Review::where('user_id', $user->id)->orderByDesc('created_at')->get();

        foreach ($reviews as $review) {
            $movie = $this->movieTitle($review->tmdb_id);
            fputcsv($fh, [
                $movie['title'], $review->created_at->format('Y-m-d'),
                $review->rating ?? '', strip_tags((string) $review->body),
                $review->has_spoiler ? 'Ya' : 'Tidak',
                $movie['director'], $movie['genres'], $movie['poster_url'],
            ]);
        }
    }

    private function writeHistoryCsv($fh, mixed $user): void
    {
        fputcsv($fh, ['Judul Film', 'Tanggal Tonton', 'Status', 'Sutradara', 'Genre', 'Poster URL']);
        $history = WatchHistory::where('user_id', $user->id)->orderByDesc('watched_at')->get();
        $statusLabels = ['watched' => 'Ditonton', 'plan_to_watch' => 'Akan Ditonton', 'watching' => 'Sedang Ditonton'];

        foreach ($history as $entry) {
            $movie = $this->movieTitle($entry->tmdb_id);
            fputcsv($fh, [
                $movie['title'], $entry->watched_at?->format('Y-m-d') ?? '',
                $statusLabels[$entry->status] ?? $entry->status,
                $movie['director'], $movie['genres'], $movie['poster_url'],
            ]);
        }
    }
}
