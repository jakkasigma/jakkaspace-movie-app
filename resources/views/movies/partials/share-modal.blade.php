<link rel="stylesheet" href="/css/intro.css">

<div id="share-modal-overlay" class="share-modal-overlay" onclick="if(event.target===this)closeShareModal()">
    <div class="share-modal" onclick="event.stopPropagation()">
        {{-- Header --}}
        <div class="share-modal-header">
            <h3 class="share-modal-title">Bagikan Film</h3>
            <button onclick="closeShareModal()" class="share-modal-close">✕</button>
        </div>

        {{-- Movie info --}}
        <div class="share-movie-info">
            @if ($movie['poster_url'])
                <img src="{{ $movie['poster_url'] }}" alt="{{ $movie['title'] }}" class="share-movie-poster">
            @endif
            <div>
                <p class="share-movie-title">{{ $movie['title'] }}</p>
                @if ($movie['release_year'] ?? null)
                    <p class="share-movie-year">{{ $movie['release_year'] }}</p>
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        <div class="share-tabs">
            <button type="button" class="share-tab active" data-tab="users" onclick="switchShareTab(this, 'users')">Ke User</button>
            <button type="button" class="share-tab" data-tab="lists" onclick="switchShareTab(this, 'lists')">Ke List</button>
            <button type="button" class="share-tab" data-tab="story" onclick="switchShareTab(this, 'story')">Story & Simpan</button>
        </div>

        {{-- Tab: Users --}}
        <div class="share-tab-content active" id="share-tab-users">
            {{-- Search --}}
            <div class="share-search-wrap">
                <input type="text" id="share-user-search" placeholder="Cari username..." class="share-search-input" oninput="filterShareUsers(this.value)">
            </div>

            {{-- Recent conversations --}}
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

        {{-- Tab: Lists --}}
        <div class="share-tab-content" id="share-tab-lists">
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

        {{-- Tab: Story & Simpan --}}
        <div class="share-tab-content" id="share-tab-story">
            <div class="story-share-preview">
                @if ($movie['poster_url'])
                    <img src="{{ $movie['poster_url'] }}" alt="" class="story-share-poster">
                @endif
                <div class="story-share-actions">
                    <p class="share-item-name">{{ $movie['title'] }}</p>
                    <button type="button" class="story-action-button story-action-primary" onclick="shareStoryTemplate()">Story Instagram</button>
                    <button type="button" class="story-action-button" onclick="downloadStoryTemplate()">Unduh PNG</button>
                    <button type="button" class="story-action-button" data-copy-link onclick="copyMovieLink()">Salin Tautan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openShareModal() {
        document.getElementById('share-modal-overlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeShareModal() {
        document.getElementById('share-modal-overlay').classList.remove('active');
        document.body.style.overflow = '';
    }

    function switchShareTab(btn, tab) {
        document.querySelectorAll('.share-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.share-tab-content').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('share-tab-' + tab).classList.add('active');
    }

    function filterShareUsers(query) {
        document.querySelectorAll('#share-user-list .share-item').forEach(item => {
            const name = item.dataset.name?.toLowerCase() || '';
            item.style.display = name.includes(query.toLowerCase()) ? '' : 'none';
        });
    }

    function shareToUser(userId) {
        const btn = event.target.closest('.share-item');
        const originalText = btn?.querySelector('.share-item-name')?.textContent || 'Mengirim...';
        document.getElementById('share-modal-overlay').classList.remove('active');
        document.body.style.overflow = '';

        fetch('{{ route('movies.share.user', $movie['id']) }}', {
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
        document.getElementById('share-modal-overlay').classList.remove('active');
        document.body.style.overflow = '';

        fetch('{{ route('movies.share.list', $movie['id']) }}', {
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
</script>
