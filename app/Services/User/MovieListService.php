<?php

namespace App\Services\User;

use App\Models\ListMember;
use App\Models\ListMessage;
use App\Models\ListMovie;
use App\Models\MovieList;
use App\Models\User;
use App\Notifications\ListInvitation;
use App\Notifications\ListJoinRequest;
use App\Services\Movie\MovieService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MovieListService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    /**
     * @param  array{name: string, description?: string|null, is_public?: bool, cover_photo?: UploadedFile|null}  $data
     */
    public function createList(User $user, array $data): MovieList
    {
        $coverPath = null;
        if (isset($data['cover_photo']) && $data['cover_photo'] instanceof UploadedFile && $user->canUploadCover()) {
            $coverPath = $data['cover_photo']->store('covers', 'public');
        }

        $list = MovieList::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_public' => $data['is_public'] ?? true,
            'code' => $this->generateCode(),
            'cover_photo' => $coverPath,
        ]);

        ListMember::create([
            'movie_list_id' => $list->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'approved',
            'joined_at' => now(),
        ]);

        $this->logActivity($list, $user, 'List dibuat oleh :name');

        return $list;
    }

    /**
     * @param  array{name?: string, description?: string|null, is_public?: bool, cover_photo?: UploadedFile|null}  $data
     */
    public function updateList(MovieList $list, array $data): MovieList
    {
        $updateData = array_filter([
            'name' => $data['name'] ?? null,
            'description' => array_key_exists('description', $data) ? $data['description'] : null,
            'is_public' => $data['is_public'] ?? null,
        ], fn (mixed $v): bool => $v !== null);

        if (isset($data['cover_photo']) && $data['cover_photo'] instanceof UploadedFile) {
            $user = auth()->user();
            if ($user && $user->canUploadCover()) {
                if ($list->cover_photo) {
                    Storage::disk('public')->delete($list->cover_photo);
                }
                $updateData['cover_photo'] = $data['cover_photo']->store('covers', 'public');
            }
        }

        $list->update($updateData);

        return $list->fresh();
    }

    public function deleteList(MovieList $list): void
    {
        if ($list->cover_photo) {
            Storage::disk('public')->delete($list->cover_photo);
        }

        $list->delete();
    }

    /**
     * @return Collection<int, MovieList>
     */
    public function getUserLists(User $user): Collection
    {
        return MovieList::where('user_id', $user->id)
            ->withCount('listMovies')
            ->latest()
            ->get();
    }

    public function addMovie(MovieList $list, int $tmdbId): ListMovie
    {
        $maxOrder = $list->listMovies()->max('sort_order') ?? 0;

        $result = ListMovie::firstOrCreate(
            ['movie_list_id' => $list->id, 'tmdb_id' => $tmdbId],
            ['sort_order' => $maxOrder + 1],
        );

        if ($result->wasRecentlyCreated) {
            [$detail] = $this->movieService->findMovie($tmdbId);
            $title = $detail['title'] ?? "Film #{$tmdbId}";

            $this->logActivity($list, auth()->user() ?? $list->user, ':name menambahkan '.$title.' ke list', [
                'tmdb_id' => $tmdbId,
                'movie_title' => $title,
            ]);
        }

        return $result;
    }

    public function removeMovie(MovieList $list, int $tmdbId): void
    {
        [$detail] = $this->movieService->findMovie($tmdbId);
        $title = $detail['title'] ?? "Film #{$tmdbId}";

        ListMovie::where('movie_list_id', $list->id)
            ->where('tmdb_id', $tmdbId)
            ->delete();

        $this->logActivity($list, auth()->user() ?? $list->user, ':name menghapus '.$title.' dari list', [
            'tmdb_id' => $tmdbId,
            'movie_title' => $title,
        ]);
    }

    public function isMovieInList(MovieList $list, int $tmdbId): bool
    {
        return ListMovie::where('movie_list_id', $list->id)
            ->where('tmdb_id', $tmdbId)
            ->exists();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMoviesInList(MovieList $list): array
    {
        $tmdbIds = $list->listMovies()
            ->orderBy('sort_order')
            ->pluck('tmdb_id')
            ->all();

        $movies = [];

        foreach ($tmdbIds as $tmdbId) {
            [$detail] = $this->movieService->findMovie((int) $tmdbId);
            if ($detail !== null) {
                $movies[] = $detail;
            }
        }

        return $movies;
    }

    // ── Membership ─────────────────────────────────────────────────────

    public function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (MovieList::where('code', $code)->exists());

        return $code;
    }

    public function joinList(MovieList $list, User $user): ListMember
    {
        $isApproved = $list->is_public;

        $member = ListMember::create([
            'movie_list_id' => $list->id,
            'user_id' => $user->id,
            'role' => 'member',
            'status' => $isApproved ? 'approved' : 'pending',
            'joined_at' => now(),
        ]);

        if ($isApproved) {
            $this->logActivity($list, $user, ':name bergabung ke list');
        } else {
            $owner = $list->user;
            $owner->notify(new ListJoinRequest($list, $user));
        }

        return $member;
    }

    public function leaveList(MovieList $list, User $user): void
    {
        ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->delete();

        $this->logActivity($list, $user, ':name keluar dari list');
    }

    public function approveMember(MovieList $list, User $user): void
    {
        ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->update(['status' => 'approved']);

        $this->logActivity($list, auth()->user() ?? $list->user, ':name menyetujui '.$user->name, [
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
        ]);
    }

    public function rejectMember(MovieList $list, User $user): void
    {
        ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    public function kickMember(MovieList $list, User $user): void
    {
        ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->where('role', '!=', 'owner')
            ->delete();

        $this->logActivity($list, auth()->user() ?? $list->user, ':name mengeluarkan '.$user->name.' dari list', [
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
        ]);
    }

    public function promoteMember(MovieList $list, User $user): void
    {
        ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->update(['role' => 'admin']);

        $this->logActivity($list, auth()->user() ?? $list->user, ':name mengangkat '.$user->name.' menjadi admin', [
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
        ]);
    }

    public function demoteMember(MovieList $list, User $user): void
    {
        ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->update(['role' => 'member']);

        $this->logActivity($list, auth()->user() ?? $list->user, ':name menurunkan '.$user->name.' menjadi member', [
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
        ]);
    }

    public function isMember(MovieList $list, User $user): bool
    {
        return ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();
    }

    public function isPendingMember(MovieList $list, User $user): bool
    {
        return ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }

    public function getMemberRole(MovieList $list, User $user): ?string
    {
        return ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->value('role');
    }

    /**
     * @return Collection<int, ListMember>
     */
    public function getMembers(MovieList $list): Collection
    {
        return ListMember::where('movie_list_id', $list->id)
            ->where('status', 'approved')
            ->with('user')
            ->get();
    }

    /**
     * @return Collection<int, ListMember>
     */
    public function getPendingMembers(MovieList $list): Collection
    {
        return ListMember::where('movie_list_id', $list->id)
            ->where('status', 'pending')
            ->with('user')
            ->get();
    }

    /**
     * @return Collection<int, ListMember>
     */
    public function getInvitedMembers(MovieList $list): Collection
    {
        return ListMember::where('movie_list_id', $list->id)
            ->where('status', 'invited')
            ->with('user')
            ->get();
    }

    // ── Invitation ─────────────────────────────────────────────────────

    public function inviteUser(MovieList $list, User $inviter, User $target): void
    {
        abort_if($inviter->id !== $list->user_id, 403, 'Only the owner can invite users.');

        $existing = ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $target->id)
            ->first();

        if ($existing) {
            abort(409, 'User already has a membership or invitation for this list.');
        }

        ListMember::create([
            'movie_list_id' => $list->id,
            'user_id' => $target->id,
            'role' => 'member',
            'status' => 'invited',
            'joined_at' => now(),
        ]);

        $target->notify(new ListInvitation($list, $inviter));
    }

    public function acceptInvitation(MovieList $list, User $user): void
    {
        $member = ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->where('status', 'invited')
            ->firstOrFail();

        $member->update(['status' => 'approved']);

        $this->logActivity($list, $user, ':name bergabung ke list');
    }

    public function declineInvitation(MovieList $list, User $user): void
    {
        ListMember::where('movie_list_id', $list->id)
            ->where('user_id', $user->id)
            ->where('status', 'invited')
            ->delete();
    }

    // ── Activity Logging ───────────────────────────────────────────────

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function logActivity(MovieList $list, User $actor, string $description, array $metadata = []): void
    {
        $message = str_replace(':name', $actor->name, $description);

        ListMessage::create([
            'movie_list_id' => $list->id,
            'user_id' => $actor->id,
            'type' => 'activity',
            'message' => $message,
            'metadata' => $metadata,
        ]);
    }
}
