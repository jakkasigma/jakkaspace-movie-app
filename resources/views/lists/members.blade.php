@php
    $approved = $list->approvedMembers()->with('user')->get();
    $pending = $list->pendingMembers()->with('user')->get();
    $invited = $list->members()->where('status', 'invited')->with('user')->get();
    $existingMemberIds = $approved->pluck('user_id')->merge($pending->pluck('user_id'))->merge($invited->pluck('user_id'))->push($list->user_id)->unique()->all();
@endphp

<div class="list-members-section">
    {{-- Pending join requests --}}
    @if ($isOwner && $pending->isNotEmpty())
        <div style="margin-bottom: 24px;">
            <h3 class="list-members-heading">Permintaan Bergabung</h3>
            @foreach ($pending as $member)
                <div class="list-member-row">
                    @include('lists._member-avatar', ['user' => $member->user])
                    <div class="list-member-info">
                        <p class="list-member-name">{{ $member->user?->name ?? 'Pengguna' }}</p>
                    </div>
                    <form method="POST" action="{{ route('lists.members.approve', [$list, $member->user_id]) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="list-member-btn list-member-btn-approve">Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('lists.members.reject', [$list, $member->user_id]) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="list-member-btn list-member-btn-reject">Tolak</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Invitations sent (owner view) --}}
    @if ($isOwner && $invited->isNotEmpty())
        <div style="margin-bottom: 24px;">
            <h3 class="list-members-heading">Undangan Terkirim</h3>
            @foreach ($invited as $member)
                <div class="list-member-row" style="opacity:0.6;">
                    @include('lists._member-avatar', ['user' => $member->user])
                    <div class="list-member-info">
                        <p class="list-member-name">{{ $member->user?->name ?? 'Pengguna' }}</p>
                        <p class="list-member-meta">⏳ Menunggu diterima</p>
                    </div>
                    <form method="POST" action="{{ route('lists.members.reject', [$list, $member->user_id]) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="list-member-btn list-member-btn-reject" style="padding:4px 10px;font-size:0.72rem;">Batalkan</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Invite button --}}
    @if ($isOwner)
        <div style="margin-bottom:24px;">
            <button onclick="document.getElementById('invite-modal').classList.add('active')" class="list-member-invite-btn">+ Undang Anggota</button>
        </div>
    @endif

    {{-- Members list --}}
    <div>
        <h3 class="list-members-heading">Anggota ({{ $approved->count() }})</h3>
        @foreach ($approved as $member)
            @php
                $user = $member->user;
                $roleLabels = ['owner' => 'Owner', 'admin' => 'Admin', 'member' => 'Member'];
                $roleColors = ['owner' => '#a3e635', 'admin' => '#60a5fa', 'member' => 'rgba(255,255,255,0.4)'];
            @endphp
            <div class="list-member-row">
                @include('lists._member-avatar', ['user' => $user])
                <div class="list-member-info">
                    <p class="list-member-name">
                        @if ($user)
                            <a href="{{ route('profile.show', $user->username) }}">{{ $user->name }}</a>
                        @else
                            Pengguna
                        @endif
                    </p>
                    <p class="list-member-meta" style="color:{{ $roleColors[$member->role] ?? 'rgba(255,255,255,0.4)' }};">
                        {{ $roleLabels[$member->role] ?? $member->role }}
                        @if ($member->role === 'owner') 👑
                        @elseif ($member->role === 'admin') 🛡️
                        @endif
                    </p>
                </div>
                @if ($isOwner && $member->role !== 'owner')
                    <div class="list-member-actions">
                        @if ($member->role === 'member')
                            <form method="POST" action="{{ route('lists.members.promote', [$list, $member->user_id]) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="list-member-btn-action" title="Jadikan Admin">🛡️</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('lists.members.demote', [$list, $member->user_id]) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="list-member-btn-action" title="Turunkan">⬇️</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('lists.members.kick', [$list, $member->user_id]) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="list-member-btn-kick" title="Keluarkan" onclick="return confirm('Keluarkan anggota ini?')">✕</button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Invite Modal --}}
@if ($isOwner)
<div id="invite-modal" class="invite-modal-overlay" onclick="if(event.target===this)this.classList.remove('active')">
    <div class="invite-modal" onclick="event.stopPropagation()">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="color:#fff;font-size:1rem;font-weight:600;">Undang Anggota</h3>
            <button onclick="document.getElementById('invite-modal').classList.remove('active')" style="background:none;border:none;color:rgba(255,255,255,0.5);font-size:1.2rem;cursor:pointer;">✕</button>
        </div>

        <form method="POST" action="{{ route('lists.members.invite', $list) }}" id="invite-form" style="margin-bottom:16px;">
            @csrf
            <div style="display:flex;gap:8px;">
                <input type="text" name="username" id="invite-username-input" placeholder="Cari atau masukkan username..." required autocomplete="off"
                    style="flex:1;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:6px;padding:10px 14px;color:#fff;font-size:0.85rem;outline:none;">
                <button type="submit" style="background:rgba(255,255,255,0.1);border:none;border-radius:6px;padding:10px 20px;color:#fff;font-size:0.82rem;font-weight:600;cursor:pointer;">Undang</button>
            </div>
        </form>

        @php
            $followList = $following ?? collect();
            $available = $followList->reject(fn ($u) => in_array($u->id, $existingMemberIds));
        @endphp
        @if ($available->isNotEmpty())
            <p style="color:rgba(255,255,255,0.4);font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Yang Kamu Ikuti</p>
            <div style="display:flex;flex-direction:column;gap:6px;max-height:300px;overflow-y:auto;" id="invite-friend-list">
                @foreach ($available as $friend)
                    <button type="button" class="invite-friend-item" data-username="{{ $friend->username }}"
                        onclick="document.getElementById('invite-username-input').value='{{ $friend->username }}'; document.getElementById('invite-form').submit();"
                        style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:rgba(255,255,255,0.03);border:1px solid transparent;border-radius:6px;cursor:pointer;text-align:left;transition:background 0.15s;width:100%;"
                        onmouseover="this.style.background='rgba(255,255,255,0.07)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'">
                        @if ($friend->avatar_url)
                            <img src="{{ $friend->avatar_url }}" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                        @else
                            <div style="width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;font-size:0.75rem;color:rgba(255,255,255,0.5);flex-shrink:0;">{{ strtoupper(substr($friend->name,0,1)) }}</div>
                        @endif
                        <div style="flex:1;min-width:0;">
                            <p style="color:#fff;font-size:0.82rem;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $friend->name }}</p>
                            <p style="font-size:0.7rem;color:rgba(255,255,255,0.35);">@ {{ $friend->username }}</p>
                        </div>
                        <span style="color:rgba(255,255,255,0.3);font-size:0.72rem;">+ Undang</span>
                    </button>
                @endforeach
            </div>
        @else
            <p style="color:rgba(255,255,255,0.3);font-size:0.82rem;text-align:center;padding:20px 0;">Tidak ada teman yang bisa diundang. Cari berdasarkan username.</p>
        @endif
    </div>
</div>

<style>
.invite-modal-overlay {
    position:fixed;inset:0;z-index:9999;
    background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);
    display:none;align-items:center;justify-content:center;
    padding:4vw;
}
.invite-modal-overlay.active { display:flex; }
.invite-modal {
    background:#111;border:1px solid rgba(255,255,255,0.1);border-radius:12px;
    padding:24px;width:100%;max-width:420px;max-height:80vh;overflow-y:auto;
}
</style>
@endif
