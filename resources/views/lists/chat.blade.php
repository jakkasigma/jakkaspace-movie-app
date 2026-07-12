@php
    $chatMessages = $list->messages()->with('user')->latest()->paginate(50);
@endphp

<div class="list-chat-container">
    <div class="list-chat-messages" id="list-chat-messages">
        @if ($chatMessages->isEmpty())
            <div class="inbox-messages-empty">
            </div>
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
                                @if ($msg->user?->avatar_url)
                                    <img src="{{ $msg->user->avatar_url }}" alt="" class="inbox-mini-avatar">
                                @else
                                    <div class="inbox-mini-avatar inbox-avatar-placeholder">
                                        {{ strtoupper(substr($msg->user?->name ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="inbox-msg-bubble {{ $msg->user?->isPlus() ? 'inbox-msg-premium' : '' }}"
                             @if ($msg->user?->isPlus() && $msg->user->theme) style="--avatar-border: {{ $msg->user->theme->avatar_border_css }}" @endif>
                            @if (! $isMine)
                                <p class="inbox-msg-sender">{{ $msg->user?->name ?? 'Pengguna' }}</p>
                            @endif
                            <p class="inbox-msg-text">{{ $msg->message }}</p>
                            <span class="inbox-msg-time">{{ $msg->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    <div class="list-chat-input-wrap">
        <form method="POST" action="{{ route('lists.chat.store', $list) }}" class="inbox-input-form" id="list-chat-form">
            @csrf
            <input type="hidden" name="type" value="text">
            <textarea name="message" id="list-chat-input" rows="1" placeholder="Ketik pesan..." required maxlength="2000" class="inbox-textarea list-chat-textarea"></textarea>
            <button type="submit" class="inbox-send-btn" title="Kirim">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const messagesEl = document.getElementById('list-chat-messages');
        const formEl = document.getElementById('list-chat-form');
        const inputEl = document.getElementById('list-chat-input');

        if (messagesEl) {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        // Auto-resize textarea
        if (inputEl) {
            inputEl.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
            inputEl.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    formEl?.requestSubmit();
                }
            });
        }

        // Echo Reverb listener
        @if (auth()->check() && $list->id)
        if (window.Echo) {
            const listId = {{ $list->id }};
            const userId = {{ auth()->id() }};

                window.Echo.private('list.' + listId)
                    .listen('.ListMessageSent', function (e) {
                        if (e.user_id === userId) return;

                        const container = document.getElementById('list-chat-messages');
                        if (!container) return;

                        const div = document.createElement('div');

                        if (e.type === 'activity') {
                            div.className = 'inbox-msg inbox-msg--activity';
                            div.innerHTML = '<div class="inbox-msg-bubble"><p class="inbox-msg-text">' + e.message + '</p></div>';
                        } else {
                            const isPremium = e.user.is_plus;
                            const pClass = isPremium ? ' inbox-msg-premium' : '';
                            const pStyle = isPremium && e.user.theme
                                ? ' style="--avatar-border:' + e.user.theme.avatar_border_css + '"'
                                : '';

                            div.className = 'inbox-msg inbox-msg--theirs';
                            const avatarHtml = e.user.avatar_url
                                ? '<img src="' + e.user.avatar_url + '" alt="" class="inbox-mini-avatar">'
                                : '<div class="inbox-mini-avatar inbox-avatar-placeholder">' + (e.user.name ? e.user.name.charAt(0) : '?') + '</div>';
                            div.innerHTML = '<div class="inbox-msg-avatar">' + avatarHtml + '</div>'
                                + '<div class="inbox-msg-bubble' + pClass + '"' + pStyle + '>'
                                + '<p class="inbox-msg-sender">' + (e.user.name || 'Pengguna') + '</p>'
                                + '<p class="inbox-msg-text">' + e.message + '</p>'
                                + '<span class="inbox-msg-time"></span>'
                                + '</div>';
                        }

                        container.appendChild(div);
                        container.scrollTop = container.scrollHeight;
                    });
        }
        @endif
    });
</script>
