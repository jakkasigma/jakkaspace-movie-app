<?php

namespace App\Services\User;

use App\Models\ActivityLog;
use App\Models\DiaryEntry;
use App\Models\User;
use App\Services\Movie\MovieService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DiaryService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    /**
     * @param  array{watched_at: string, notes?: string|null, mood?: string|null, is_rewatch?: bool}  $data
     */
    public function createEntry(User $user, int $tmdbId, array $data): DiaryEntry
    {
        [$detail] = $this->movieService->findMovie($tmdbId);

        $title = $detail['title'] ?? "Film #{$tmdbId}";
        $posterUrl = $detail['poster_url'] ?? null;

        $entry = DiaryEntry::create([
            'user_id' => $user->id,
            'tmdb_id' => $tmdbId,
            'movie_title' => $title,
            'watched_at' => $data['watched_at'],
            'notes' => $data['notes'] ?? null,
            'mood' => $data['mood'] ?? null,
            'is_rewatch' => $data['is_rewatch'] ?? false,
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'diary',
            'description' => "Menambahkan diary untuk {$title}",
            'metadata' => [
                'tmdb_id' => $tmdbId,
                'movie_title' => $title,
                'poster_url' => $posterUrl,
                'notes' => $data['notes'] ?? null,
                'mood' => $data['mood'] ?? null,
                'is_rewatch' => $data['is_rewatch'] ?? false,
            ],
            'created_at' => now(),
        ]);

        return $entry;
    }

    /**
     * @param  array{watched_at?: string, notes?: string|null, mood?: string|null, is_rewatch?: bool}  $data
     */
    public function updateEntry(DiaryEntry $entry, array $data): DiaryEntry
    {
        $entry->update(array_filter([
            'watched_at' => $data['watched_at'] ?? null,
            'notes' => array_key_exists('notes', $data) ? $data['notes'] : null,
            'mood' => array_key_exists('mood', $data) ? $data['mood'] : null,
            'is_rewatch' => $data['is_rewatch'] ?? null,
        ], fn (mixed $value): bool => $value !== null));

        $fresh = $entry->fresh();

        [$detail] = $this->movieService->findMovie($fresh->tmdb_id);

        ActivityLog::create([
            'user_id' => $fresh->user_id,
            'type' => 'diary',
            'description' => "Memperbarui diary untuk {$fresh->movie_title}",
            'metadata' => [
                'tmdb_id' => $fresh->tmdb_id,
                'movie_title' => $fresh->movie_title,
                'poster_url' => $detail['poster_url'] ?? null,
                'notes' => $fresh->notes,
                'mood' => $fresh->mood,
                'is_rewatch' => $fresh->is_rewatch,
            ],
            'created_at' => now(),
        ]);

        return $fresh;
    }

    public function deleteEntry(DiaryEntry $entry): void
    {
        $entry->delete();
    }

    public function getUserEntries(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return DiaryEntry::where('user_id', $user->id)
            ->orderByDesc('watched_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, DiaryEntry>
     */
    public function getEntriesForMovie(User $user, int $tmdbId): Collection
    {
        return DiaryEntry::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->orderByDesc('watched_at')
            ->get();
    }
}
