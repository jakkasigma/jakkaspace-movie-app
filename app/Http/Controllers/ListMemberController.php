<?php

namespace App\Http\Controllers;

use App\Models\MovieList;
use App\Models\User;
use App\Services\User\MovieListService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ListMemberController extends Controller
{
    public function __construct(
        private readonly MovieListService $listService,
    ) {}

    public function join(Request $request, MovieList $list): RedirectResponse
    {
        $user = $request->user();
        $isMember = $this->listService->isMember($list, $user);
        $isPending = $this->listService->isPendingMember($list, $user);

        if ($isMember || $isPending) {
            return redirect()->back()->with('info', 'Kamu sudah '.($isMember ? 'menjadi anggota' : 'mengirim permintaan').' list ini.');
        }

        abort_unless($list->is_public, 403, 'List ini private.');

        $this->listService->joinList($list, $user);

        return redirect()->back()->with('success', 'Berhasil bergabung ke list!');
    }

    public function joinByCode(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:8']]);

        $list = MovieList::where('code', $request->input('code'))->firstOrFail();

        return $this->join($request, $list);
    }

    public function leave(Request $request, MovieList $list): RedirectResponse
    {
        $user = $request->user();

        abort_unless($this->listService->isMember($list, $user), 403);

        $role = $this->listService->getMemberRole($list, $user);
        abort_if($role === 'owner', 403, 'Owner tidak bisa keluar dari list.');

        $this->listService->leaveList($list, $user);

        return redirect()->route('your-space.lists')->with('success', 'Berhasil keluar dari list.');
    }

    public function manage(Request $request, MovieList $list): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        return redirect()->route('lists.show', ['list' => $list, 'tab' => 'members']);
    }

    public function approve(Request $request, MovieList $list, int $userId): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $user = User::findOrFail($userId);
        $this->listService->approveMember($list, $user);

        return redirect()->back()->with('success', "{$user->name} disetujui.");
    }

    public function reject(Request $request, MovieList $list, int $userId): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $user = User::findOrFail($userId);
        $this->listService->rejectMember($list, $user);

        return redirect()->back()->with('success', "Permintaan {$user->name} ditolak.");
    }

    public function kick(Request $request, MovieList $list, int $userId): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $user = User::findOrFail($userId);
        $this->listService->kickMember($list, $user);

        return redirect()->back()->with('success', "{$user->name} dikeluarkan.");
    }

    public function promote(Request $request, MovieList $list, int $userId): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $user = User::findOrFail($userId);
        $this->listService->promoteMember($list, $user);

        return redirect()->back()->with('success', "{$user->name} dijadikan admin.");
    }

    public function demote(Request $request, MovieList $list, int $userId): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $user = User::findOrFail($userId);
        $this->listService->demoteMember($list, $user);

        return redirect()->back()->with('success', "{$user->name} dikembalikan menjadi member.");
    }

    public function invite(Request $request, MovieList $list): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $request->validate(['username' => ['required', 'string', 'max:255']]);

        $target = User::where('username', $request->input('username'))->firstOrFail();

        abort_if($target->id === $request->user()->id, 422, 'Tidak bisa mengundang diri sendiri.');

        $this->listService->inviteUser($list, $request->user(), $target);

        return redirect()->back()->with('success', "Undangan dikirim ke {$target->name}.");
    }

    public function acceptInvite(Request $request, MovieList $list): RedirectResponse
    {
        $this->listService->acceptInvitation($list, $request->user());

        return redirect()->route('lists.show', $list)->with('success', 'Kamu bergabung ke list.');
    }

    public function declineInvite(Request $request, MovieList $list): RedirectResponse
    {
        $this->listService->declineInvitation($list, $request->user());

        return redirect()->back()->with('success', 'Undangan ditolak.');
    }
}
