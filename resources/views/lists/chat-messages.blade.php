@php
    $chatMessages = $list->messages()->with('user')->latest()->paginate(50);
@endphp

<div class="list-chat-messages" id="list-chat-messages">
    @if ($chatMessages->isEmpty())
        <div class="inbox-messages-empty"></div>
    @else
        @php $lastDate = null; @endphp
        @foreach ($chatMessages->reverse() as $msg)
            @php
                $isActivity = $msg->type === 'activity';
                $isMine = ! $isActivity && $msg->user_id === auth()->id();
                $msgDate = $msg->created_at->format('Y-m-d');
            @endphp

            @if ($msgDate !== $lastDate)
                @php $lastDate = $msgDate; @endphp
                <div class="inbox-date-sep">
                    <span>
                        @if ($msg->created_at->isToday())
                            Hari Ini
                        @elseif ($msg->created_at->isYesterday())
                            Kemarin
                        @else
                            {{ $msg->created_at->format('d/m/Y') }}
                        @endif
                    </span>
                </div>
            @endif

            @if ($isActivity)
                <div class="inbox-msg inbox-msg--activity">
                    <div class="inbox-msg-bubble">
                        <p class="inbox-msg-text">{{ $msg->message }}</p>
                    </div>
                </div>
            @else
                <div class="inbox-msg {{ $isMine ? 'inbox-msg--mine' : 'inbox-msg--theirs' }}">
                        @if (! $isMine)
                            <div class="inbox-msg-avatar">
                                @php $avatarPremium = $msg->user?->isPlus() && $msg->user->theme; @endphp
                                @if ($avatarPremium)
                                    <div class="inbox-mini-premium" style="--avatar-border: {{ $msg->user->theme->avatar_border_css }}">
                                @endif
                                @if ($msg->user?->avatar_url)
                                    <img src="{{ $msg->user->avatar_url }}" alt="" class="inbox-mini-avatar" onerror="this.onerror=null;this.style.display='none'">
                                @else
                                    <div class="inbox-mini-avatar inbox-avatar-placeholder">
                                        {{ strtoupper(substr($msg->user?->name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                                @if ($avatarPremium)
                                    </div>
                                @endif
                            </div>
                        @endif
                    <div class="inbox-msg-bubble {{ ($msg->user?->isPlus() && $msg->user->theme) ? 'inbox-msg-premium' : '' }}"
                         @if ($msg->user?->isPlus() && $msg->user->theme) style="--accent-color: {{ $msg->user->theme->accent_color }};" @endif>
                        @if (! $isMine)
                            <p class="inbox-msg-sender">{{ $msg->user?->name ?? 'Pengguna' }}</p>
                        @endif
                        @if ($msg->type === 'film_share' && $msg->tmdb_id)
                            @php $meta = $msg->metadata ?? []; @endphp
                            <a href="{{ route('movies.show', $msg->tmdb_id) }}" class="list-film-share">
                                @if (! empty($meta['poster_url']))
                                    <img src="{{ $meta['poster_url'] }}" alt="" class="list-film-share-poster">
                                @else
                                    <div class="list-film-share-poster" style="background:rgba(255,255,255,0.06);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">🎬</div>
                                @endif
                                <div class="list-film-share-info">
                                    <p class="list-film-share-kicker">Berbagi Film</p>
                                    <p class="list-film-share-title">{{ $meta['title'] ?? $msg->message }}</p>
                                    @if (! empty($meta['release_year']))
                                        <p class="list-film-share-year">{{ $meta['release_year'] }}</p>
                                    @endif
                                </div>
                            </a>
                        @else
                            <p class="inbox-msg-text">{{ $msg->message }}</p>
                        @endif
                        <span class="inbox-msg-time">{{ $msg->created_at->format('H:i') }}</span>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const messagesEl = document.getElementById('list-chat-messages');
        if (messagesEl) {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
    });
</script>
