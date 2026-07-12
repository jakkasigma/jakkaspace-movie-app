@php
    $approved = $list->approvedMembers()->with('user')->get();
    $pending = $list->pendingMembers()->with('user')->get();
    $invited = $list->members()->where('status', 'invited')->with('user')->get();
    $existingMemberIds = $approved->pluck('user_id')->merge($pending->pluck('user_id'))->merge($invited->pluck('user_id'))->push($list->user_id)->unique()->all();
@endphp

<div class="list-members-section">
    {{-- Pending join requests --}}
    @if ($isOwner && $pending->isNotEmpty())
        <div class="list-member-section">
            <h3 class="list-members-heading">Permintaan Bergabung</h3>
            @foreach ($pending as $member)
                <div class="list-member-row">
                    @include('lists._member-avatar', ['user' => $member->user])
                    <div class="list-member-info">
                        <p class="list-member-name" style="color:#fff;">{{ $member->user?->name ?? 'Pengguna' }}</p>
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
        <div class="list-member-section">
            <h3 class="list-members-heading">Undangan Terkirim</h3>
            @foreach ($invited as $member)
                <div class="list-member-row" style="opacity:0.55;">
                    @include('lists._member-avatar', ['user' => $member->user])
                    <div class="list-member-info">
                        <p class="list-member-name" style="color:#fff;">{{ $member->user?->name ?? 'Pengguna' }}</p>
                        <p class="list-member-meta list-member-role-member">⏳ Menunggu diterima</p>
                    </div>
                    <form method="POST" action="{{ route('lists.members.reject', [$list, $member->user_id]) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="list-member-btn list-member-btn-reject" style="padding:4px 10px;font-size:0.7rem;">Batalkan</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Invite button --}}
    @if ($isOwner)
        <div class="list-member-section">
            <button onclick="document.getElementById('invite-modal').classList.add('active')" class="list-member-invite-btn">+ Undang Anggota</button>
        </div>
    @endif

    {{-- Members list --}}
    <div class="list-member-section">
        <h3 class="list-members-heading">Anggota ({{ $approved->count() }})</h3>
        @foreach ($approved as $member)
            @php
                $user = $member->user;
                $roleKey = $member->role;
                $roleLabels = ['owner' => 'Owner', 'admin' => 'Admin', 'member' => 'Member'];
                $roleClasses = ['owner' => 'list-member-role-owner', 'admin' => 'list-member-role-admin', 'member' => 'list-member-role-member'];
                $roleIcons = ['owner' => '👑', 'admin' => '🛡️', 'member' => ''];
            @endphp
            <div class="list-member-row">
                @include('lists._member-avatar', ['user' => $user])
                <div class="list-member-info">
                    <p class="list-member-name">
                        @if ($user)
                            <a href="{{ route('profile.show', $user->username) }}">{{ $user->name }}</a>
                        @else
                            <span style="color:#fff;">Pengguna</span>
                        @endif
                    </p>
                    <p class="list-member-meta {{ $roleClasses[$roleKey] ?? 'list-member-role-member' }}">
                        {{ $roleIcons[$roleKey] ?? '' }} {{ $roleLabels[$roleKey] ?? $roleKey }}
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
        <div class="invite-modal-header">
            <h3 class="invite-modal-title">Undang Anggota</h3>
            <button onclick="document.getElementById('invite-modal').classList.remove('active')" class="invite-modal-close">✕</button>
        </div>

        <form method="POST" action="{{ route('lists.members.invite', $list) }}" id="invite-form">
            @csrf
            <div class="invite-form-row">
                <input type="text" name="username" id="invite-username-input" placeholder="Cari atau masukkan username..." required autocomplete="off" class="invite-input">
                <button type="submit" class="invite-submit-btn">Undang</button>
            </div>
        </form>

        @php
            $followList = $following ?? collect();
            $available = $followList->reject(fn ($u) => in_array($u->id, $existingMemberIds));
        @endphp
        @if ($available->isNotEmpty())
            <p class="invite-friend-label">Yang Kamu Ikuti</p>
            <div class="invite-friend-list" id="invite-friend-list">
                @foreach ($available as $friend)
                    <button type="button" class="invite-friend-item" data-username="{{ $friend->username }}"
                        onclick="document.getElementById('invite-username-input').value='{{ $friend->username }}'; document.getElementById('invite-form').submit();">
                        @if ($friend->avatar_url)
                            <img src="{{ $friend->avatar_url }}" alt="" class="invite-friend-avatar">
                        @else
                            <div class="invite-friend-avatar invite-friend-avatar-placeholder">{{ strtoupper(substr($friend->name, 0, 1)) }}</div>
                        @endif
                        <div class="invite-friend-info">
                            <p class="invite-friend-name">{{ $friend->name }}</p>
                            <p class="invite-friend-username">@ {{ $friend->username }}</p>
                        </div>
                        <span class="invite-friend-add">+ Undang</span>
                    </button>
                @endforeach
            </div>
        @else
            <p class="invite-empty">Tidak ada teman yang bisa diundang. Cari berdasarkan username.</p>
        @endif
    </div>
</div>

<style>
.invite-modal-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);
    display: none; align-items: center; justify-content: center;
    padding: 4vw;
}
.invite-modal-overlay.active { display: flex; }
.invite-modal {
    background: #111; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;
    padding: 24px; width: 100%; max-width: 420px; max-height: 80vh; overflow-y: auto;
}
.invite-modal-header {
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;
}
.invite-modal-title {
    color: #fff; font-size: 1rem; font-weight: 600;
}
.invite-modal-close {
    background: none; border: none; color: rgba(255,255,255,0.5); font-size: 1.2rem; cursor: pointer;
}
.invite-form-row {
    display: flex; gap: 8px; margin-bottom: 16px;
}
.invite-input {
    flex: 1; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12);
    border-radius: 6px; padding: 10px 14px; color: #fff; font-size: 0.85rem; outline: none;
}
.invite-submit-btn {
    background: rgba(255,255,255,0.1); border: none; border-radius: 6px;
    padding: 10px 20px; color: #fff; font-size: 0.82rem; font-weight: 600; cursor: pointer;
}
.invite-friend-label {
    color: rgba(255,255,255,0.4); font-size: 0.75rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;
}
.invite-friend-list {
    display: flex; flex-direction: column; gap: 6px; max-height: 300px; overflow-y: auto;
}
.invite-friend-item {
    display: flex; align-items: center; gap: 10px; padding: 8px 12px;
    background: rgba(255,255,255,0.03); border: 1px solid transparent; border-radius: 6px;
    cursor: pointer; text-align: left; transition: background 0.15s; width: 100%;
}
.invite-friend-item:hover { background: rgba(255,255,255,0.07); }
.invite-friend-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.invite-friend-avatar-placeholder {
    width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.08);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; color: rgba(255,255,255,0.5);
}
.invite-friend-info { flex: 1; min-width: 0; }
.invite-friend-name {
    color: #fff; font-size: 0.82rem; font-weight: 500;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.invite-friend-username { font-size: 0.7rem; color: rgba(255,255,255,0.35); }
.invite-friend-add { color: rgba(255,255,255,0.3); font-size: 0.72rem; }
.invite-empty { color: rgba(255,255,255,0.3); font-size: 0.82rem; text-align: center; padding: 20px 0; }
</style>
@endif
