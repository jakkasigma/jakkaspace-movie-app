<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\User\FollowService;
use App\Services\User\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        private readonly FollowService $followService,
        private readonly ProfileService $profileService,
    ) {}

    public function store(Request $request, User $user): RedirectResponse
    {
        $this->followService->follow($request->user(), $user);

        return redirect()->back();
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->followService->unfollow($request->user(), $user);

        return redirect()->back();
    }

    public function followers(string $username): View
    {
        $profile = $this->profileService->findByUsername($username);

        abort_if($profile === null, 404);

        $followers = $this->followService->getFollowers($profile);

        return view('profile.followers', [
            'profile' => $profile,
            'users' => $followers,
        ]);
    }

    public function following(string $username): View
    {
        $profile = $this->profileService->findByUsername($username);

        abort_if($profile === null, 404);

        $following = $this->followService->getFollowing($profile);

        return view('profile.following', [
            'profile' => $profile,
            'users' => $following,
        ]);
    }
}
