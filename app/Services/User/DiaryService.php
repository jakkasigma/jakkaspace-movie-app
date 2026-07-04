<?php

namespace App\Services\User;

use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DiaryService
{
    /**
     * @param  array{watched_at: string, notes?: string|null, mood?: string|null, is_rewatch?: bool}  $data
     */
    public function createEntry(User $user, int $tmdbId, array $data): DiaryEntry
    {
        return DiaryEntry::create([
            'user_id' => $user->id,
            'tmdb_id' => $tmdbId,
            'watched_at' => $data['watched_at'],
            'notes' => $data['notes'] ?? null,
            'mood' => $data['mood'] ?? null,
            'is_rewatch' => $data['is_rewatch'] ?? false,
        ]);
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

        return $entry->fresh();
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
