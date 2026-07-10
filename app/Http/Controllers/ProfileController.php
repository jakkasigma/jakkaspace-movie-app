<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use App\Services\User\FollowService;
use App\Services\User\PinnedMovieService;
use App\Services\User\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
        private readonly FollowService $followService,
        private readonly PinnedMovieService $pinnedService,
    ) {}

    public function show(Request $request, string $username): View
    {
        $profile = $this->profileService->findByUsername($username);

        abort_if($profile === null, 404);

        $stats = $this->profileService->getStats($profile);

        $activeTab = $request->query('tab', 'pinned');

        $tabData = match ($activeTab) {
            'reviews' => $this->profileService->getReviewedMovies($profile),
            'lists' => $this->profileService->getPublicLists($profile),
            'favorites' => $this->profileService->getFavoritedMovies($profile),
            default => $this->pinnedService->getPinnedMovies($profile),
        };

        $viewer = $request->user();
        $isFollowing = $viewer !== null && $viewer->id !== $profile->id
            ? $this->followService->isFollowing($viewer, $profile)
            : false;

        $isPinned = false;
        $pinnedCount = 0;
        if ($viewer !== null) {
            $pinnedCount = $this->pinnedService->getPinnedCount($viewer);
        }

        return view('profile.show', [
            'profile' => $profile,
            'stats' => $stats,
            'activeTab' => $activeTab,
            'tabData' => $tabData,
            'isFollowing' => $isFollowing,
            'isSelf' => $viewer?->id === $profile->id,
            'pinnedCount' => $pinnedCount,
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->name = $request->validated('name');
        $user->username = $request->validated('username') ?: null;
        $user->bio = $request->validated('bio') ?: null;
        $user->is_private = $request->boolean('is_private');

        if ($user->email !== $request->validated('email')) {
            $user->email = $request->validated('email');
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            // Hapus avatar lama kalau bukan dari Google (avatar_url lokal)
            if ($user->avatar && ! str_starts_with((string) $user->avatar_url, 'https://')) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');

            if ($path === false) {
                return back()->withErrors(['avatar' => 'Upload foto gagal. Coba lagi.'])->withInput();
            }

            $user->avatar = $path;
            $user->avatar_url = '/storage/'.$path;
        }

        $user->save();

        $changes = $user->getChanges();

        foreach ($changes as $field => $newValue) {
            if (! in_array($field, ['name', 'username', 'bio', 'avatar', 'avatar_url', 'email', 'email_verified_at'])) {
                continue;
            }

            $original = $user->getOriginal($field);

            if ($field === 'avatar' || $field === 'avatar_url') {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'type' => 'profile_update',
                    'description' => 'Mengubah foto profil',
                    'metadata' => ['field' => 'avatar'],
                    'created_at' => now(),
                ]);
            } elseif ($field === 'email') {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'type' => 'profile_update',
                    'description' => "Mengubah email menjadi {$newValue}",
                    'metadata' => ['field' => $field, 'old_value' => $original, 'new_value' => $newValue],
                    'created_at' => now(),
                ]);
            } else {
                $label = [
                    'name' => 'nama',
                    'username' => 'username',
                    'bio' => 'bio',
                    'is_private' => 'visibilitas akun',
                ][$field] ?? $field;

                if ($field === 'bio') {
                    ActivityLog::create([
                        'user_id' => $user->id,
                        'type' => 'profile_update',
                        'description' => 'Mengubah bio',
                        'metadata' => ['field' => $field, 'old_value' => $original, 'new_value' => $newValue],
                        'created_at' => now(),
                    ]);
                } else {
                    ActivityLog::create([
                        'user_id' => $user->id,
                        'type' => 'profile_update',
                        'description' => "Mengubah {$label} menjadi {$newValue}",
                        'metadata' => ['field' => $field, 'old_value' => $original, 'new_value' => $newValue],
                        'created_at' => now(),
                    ]);
                }
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
