<form method="POST" action="{{ route('lists.chat.store', $list) }}" class="inbox-input-form" id="list-chat-form">
    @csrf
    <input type="hidden" name="type" value="text">
    <textarea name="message" id="list-chat-input" rows="1" placeholder="Ketik pesan..." required maxlength="2000" class="inbox-textarea"></textarea>
    <button type="submit" class="inbox-send-btn" title="Kirim">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="22" y1="2" x2="11" y2="13"/>
            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
    </button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formEl = document.getElementById('list-chat-form');
        const inputEl = document.getElementById('list-chat-input');

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
                        const isPremium = e.user.is_plus && e.user.theme;
                        const pClass = isPremium ? ' inbox-msg-premium' : '';
                        const pStyle = isPremium
                            ? ' style="--accent-color:' + e.user.theme.accent_color + '"'
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
