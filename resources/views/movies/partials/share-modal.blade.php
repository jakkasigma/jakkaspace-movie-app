<link rel="stylesheet" href="/css/intro.css">

<div id="share-modal-overlay" class="share-modal-overlay" onclick="if(event.target===this)closeShareModal()">
    <div class="share-modal share-layout-modal" onclick="event.stopPropagation()">
        {{-- Left: Poster --}}
        <div class="share-left">
            @if (($movie['poster_url'] ?? null))
                <img src="{{ $movie['poster_url'] ?? '' }}" alt="" class="share-left-poster">
            @else
                <div class="share-left-poster share-left-poster-empty">No Poster</div>
            @endif
        </div>

        {{-- Right: Content --}}
        <div class="share-right">
            <div class="share-right-header">
                <h3 class="share-modal-title">Bagikan Film</h3>
                <button onclick="closeShareModal()" class="share-modal-close">✕</button>
            </div>

            {{-- Main menu --}}
            <div class="share-menu" id="share-menu-main">
                <button type="button" class="share-menu-item" onclick="showSubview('users')">
                    <span class="share-menu-icon">👤</span>
                    <span class="share-menu-label">Ke User</span>
                    <span class="share-menu-desc">Kirim lewat pesan</span>
                </button>
                <button type="button" class="share-menu-item" onclick="showSubview('lists')">
                    <span class="share-menu-icon">📋</span>
                    <span class="share-menu-label">Ke List</span>
                    <span class="share-menu-desc">Kirim ke list film</span>
                </button>
                <button type="button" class="share-menu-item" onclick="shareStoryTemplate()">
                    <span class="share-menu-icon">📸</span>
                    <span class="share-menu-label">Story Instagram</span>
                    <span class="share-menu-desc">Bagikan ke IG / Download PNG</span>
                </button>
                <button type="button" class="share-menu-item" data-copy-link onclick="copyMovieLink()">
                    <span class="share-menu-icon">🔗</span>
                    <span class="share-menu-label">Salin Tautan</span>
                    <span class="share-menu-desc">Copy link film</span>
                </button>
            </div>

            {{-- Sub-view: Users --}}
            <div class="share-subview" id="share-subview-users">
                <button type="button" class="share-subview-back" onclick="showMainMenu()">← Kembali</button>
                <div class="share-search-wrap">
                    <input type="text" id="share-user-search" placeholder="Cari username..." class="share-search-input" oninput="filterShareUsers(this.value)">
                </div>
                <div class="share-list" id="share-user-list">
                    @forelse ($conversations as $conv)
                        <button type="button" class="share-item" data-user-id="{{ $conv['user_id'] }}" data-name="{{ $conv['name'] }}" onclick="shareToUser({{ $conv['user_id'] }})">
                            @if ($conv['is_plus'] && $conv['theme'])
                                <div class="share-item-avatar-premium" style="--avatar-border: {{ $conv['theme']->avatar_border_css }}">
                                    @if ($conv['avatar_url'])
                                        <img src="{{ $conv['avatar_url'] }}" alt="" class="share-item-avatar" onerror="this.onerror=null;this.style.display='none'">
                                    @else
                                        <div class="share-item-avatar share-item-avatar-placeholder">{{ strtoupper(substr($conv['name'], 0, 1)) }}</div>
                                    @endif
                                </div>
                            @else
                                @if ($conv['avatar_url'])
                                    <img src="{{ $conv['avatar_url'] }}" alt="" class="share-item-avatar" onerror="this.onerror=null;this.style.display='none'">
                                @else
                                    <div class="share-item-avatar share-item-avatar-placeholder">{{ strtoupper(substr($conv['name'], 0, 1)) }}</div>
                                @endif
                            @endif
                            <div class="share-item-info">
                                <p class="share-item-name">{{ $conv['name'] }}</p>
                                @if ($conv['username'])
                                    <p class="share-item-meta">@ {{ $conv['username'] }}</p>
                                @endif
                            </div>
                        </button>
                    @empty
                        <p class="share-empty">Belum ada percakapan.</p>
                    @endforelse
                </div>
            </div>

            {{-- Sub-view: Lists --}}
            <div class="share-subview" id="share-subview-lists">
                <button type="button" class="share-subview-back" onclick="showMainMenu()">← Kembali</button>
                <div class="share-list">
                    @forelse ($joinedLists as $list)
                        <button type="button" class="share-item" onclick="shareToList({{ $list->id }})">
                            <div class="share-item-avatar share-item-avatar-list">{{ strtoupper(substr($list->name, 0, 1)) }}</div>
                            <div class="share-item-info">
                                <p class="share-item-name">{{ $list->name }}</p>
                                <p class="share-item-meta">{{ $list->list_movies_count }} film</p>
                            </div>
                        </button>
                    @empty
                        <p class="share-empty">Belum bergabung ke list manapun.</p>
                    @endforelse
                </div>
            </div>

            {{-- Sub-view: Story --}}
            <div class="share-subview" id="share-subview-story">
                <button type="button" class="share-subview-back" onclick="showMainMenu()">← Kembali</button>
                <div class="story-share-preview">
                    @if (($movie['poster_url'] ?? null))
                        <img src="{{ $movie['poster_url'] ?? '' }}" alt="" class="story-share-poster">
                    @endif
                    <div class="story-share-actions">
                        <p class="share-item-name">{{ $movie['title'] ?? '' }}</p>
                        <button type="button" class="story-action-button story-action-primary" onclick="shareStoryTemplate()">Story Instagram</button>
                        <button type="button" class="story-action-button" onclick="downloadStoryTemplate()">Unduh PNG</button>
                        <button type="button" class="story-action-button" data-copy-link onclick="copyMovieLink()">Salin Tautan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.openShareModal = function() {
        const overlay = document.getElementById('share-modal-overlay');
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            showMainMenu();
        }
    };

    window.closeShareModal = function() {
        const overlay = document.getElementById('share-modal-overlay');
        if (overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    function showMainMenu() {
        document.querySelectorAll('.share-subview').forEach(v => v.classList.remove('active'));
        document.getElementById('share-menu-main').classList.add('active');
    }

    function showSubview(name) {
        document.getElementById('share-menu-main').classList.remove('active');
        document.querySelectorAll('.share-subview').forEach(v => v.classList.remove('active'));
        document.getElementById('share-subview-' + name).classList.add('active');
    }

    window.shareStoryTemplate = window.shareStoryTemplate || shareStoryTemplate;
    window.downloadStoryTemplate = window.downloadStoryTemplate || downloadStoryTemplate;

    function filterShareUsers(query) {
        document.querySelectorAll('#share-user-list .share-item').forEach(item => {
            const name = item.dataset.name?.toLowerCase() || '';
            item.style.display = name.includes(query.toLowerCase()) ? '' : 'none';
        });
    }

    function shareToUser(userId) {
        closeShareModal();

        fetch('{{ route('movies.share.user', $movieId) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        });
    }

    function shareToList(listId) {
        closeShareModal();

        fetch('{{ route('movies.share.list', $movieId) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ list_id: listId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        });
    }

    // Fallback: jika shareStoryTemplate / downloadStoryTemplate belum siap dari welcome.js
    if (typeof shareStoryTemplate !== 'function') {
        window.shareStoryTemplate = function() {
            const btn = document.querySelector('[data-share-story]');
            if (btn) btn.click();
        };
    }
    if (typeof downloadStoryTemplate !== 'function') {
        window.downloadStoryTemplate = function() {
            const btn = document.querySelector('[data-story-download]');
            if (btn) btn.click();
        };
    }
</script>
