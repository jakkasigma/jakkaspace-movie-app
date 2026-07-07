@if ($user?->avatar_url)
    <img src="{{ $user->avatar_url }}" alt="" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
@else
    <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: rgba(255,255,255,0.5); flex-shrink: 0;">
        {{ strtoupper(substr($user?->name ?? '?', 0, 1)) }}
    </div>
@endif
