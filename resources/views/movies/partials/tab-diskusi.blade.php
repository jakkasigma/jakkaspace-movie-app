<div class="detail-extra detail-tab-diskusi">
    {{-- Header: filter + write button --}}
    <div class="diskusi-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
        <div class="diskusi-filters">
            <a href="{{ route('movies.show', $movieId) }}?tab=diskusi&sort=recent"
               class="{{ $sort === 'recent' ? 'filter-active' : '' }}">Terbaru</a>
            <a href="{{ route('movies.show', $movieId) }}?tab=diskusi&sort=popular"
               class="{{ $sort === 'popular' ? 'filter-active' : '' }}">Terpopuler</a>
        </div>
        @auth
            <a href="{{ route('movies.show', $movieId) }}?tab=info#review-form" class="user-action-btn">
                ✏️ Tulis Review
            </a>
        @else
            <a href="{{ route('login') }}" class="user-action-login-prompt">
                Masuk untuk menulis review →
            </a>
        @endauth
    </div>

    {{-- Review + Comments unified feed --}}
    <div class="detail-reviews-list">
        @forelse ($communityReviews as $review)
            {{-- Review card --}}
            <article class="detail-review-card {{ $review->user?->isPlus() ? 'item-premium' : '' }}"
                     @if ($review->user?->isPlus() && $review->user->theme) style="--item-accent: {{ $review->user->theme->accent_color }}" @endif>
                <div class="detail-review-header">
                    <div class="detail-review-author">
                        @if ($review->user?->avatar_url)
                            <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}" class="detail-review-avatar">
                        @else
                            <div class="detail-review-avatar detail-review-avatar-placeholder">
                                {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            @if ($review->user?->username)
                                <a href="{{ route('profile.show', $review->user->username) }}" class="detail-review-name">{{ $review->user->name }}</a>
                            @else
                                <span class="detail-review-name">{{ $review->user?->name ?? 'Pengguna' }}</span>
                            @endif
                            <span class="detail-review-date">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="detail-review-meta">
                        @if ($review->rating)
                            <span class="detail-review-rating">★ {{ $review->rating }}/5</span>
                        @endif
                    </div>
                </div>
                @if ($review->body)
                    @if ($review->has_spoiler)
                        <p class="detail-review-spoiler">⚠ Mengandung spoiler</p>
                    @endif
                    <p class="detail-review-body">{!! $review->parsed_body !!}</p>
                @endif
                <div class="detail-review-footer">
                    <span class="detail-review-likes">♡ {{ $review->likes_count }}</span>
                    @auth
                        <button type="button" class="detail-reply-btn"
                                data-review-id="{{ $review->id }}"
                                data-parent-id=""
                                data-username="{{ $review->user?->username ?? '' }}">Balas</button>
                    @endauth
                </div>
            </article>

            {{-- Top-level comments --}}
            @foreach ($review->comments as $comment)
                @php $commentPlus = $comment->user?->isPlus() && $comment->user->theme; @endphp
                <article class="detail-comment {{ $comment->user?->isPlus() ? 'item-premium' : '' }}"
                         style="margin-left: 24px; margin-top: 16px; padding-left: 16px; border-left: 2px solid #e5e5e5;{{ $commentPlus ? '--item-accent:' . $comment->user->theme->accent_color : '' }}">
                    <div class="detail-comment-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                        @if ($comment->user?->avatar_url)
                            <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                        @else
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; color: #666;">
                                {{ strtoupper(substr($comment->user?->name ?? '?', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            @if ($comment->user?->username)
                                <a href="{{ route('profile.show', $comment->user->username) }}" style="font-weight: 600; color: #333; text-decoration: none;">{{ $comment->user->name }}</a>
                            @else
                                <span style="font-weight: 600; color: #333;">{{ $comment->user?->name ?? 'Pengguna' }}</span>
                            @endif
                            <span style="margin-left: 8px; font-size: 13px; color: #999;">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <p style="margin: 0 0 8px 0; line-height: 1.5; color: #333;">{!! $comment->parsed_body !!}</p>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 13px; color: #666;">
                        <span>♡ {{ $comment->likes_count ?? 0 }}</span>
                        @auth
                            <button type="button" class="detail-reply-btn"
                                    data-review-id="{{ $review->id }}"
                                    data-parent-id="{{ $comment->id }}"
                                    data-username="{{ $comment->user?->username ?? '' }}"
                                    style="background: none; border: none; color: #666; cursor: pointer; padding: 4px 0; font-weight: 600; font-size: 13px;">Balas</button>
                        @endauth
                    </div>
                </article>

                {{-- All nested replies flattened to 1 level indentation --}}
                @foreach ($comment->getAllReplies() as $reply)
                    @php $replyPlus = $reply->user?->isPlus() && $reply->user->theme; @endphp
                    <article class="detail-comment-reply {{ $reply->user?->isPlus() ? 'item-premium' : '' }}"
                             style="margin-left: 48px; margin-top: 12px; padding-left: 16px; border-left: 2px solid #e5e5e5;{{ $replyPlus ? '--item-accent:' . $reply->user->theme->accent_color : '' }}">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            @if ($reply->user?->avatar_url)
                                <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->name }}" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                            @else
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; color: #666;">
                                    {{ strtoupper(substr($reply->user?->name ?? '?', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                @if ($reply->user?->username)
                                    <a href="{{ route('profile.show', $reply->user->username) }}" style="font-weight: 600; color: #333; text-decoration: none; font-size: 14px;">{{ $reply->user->name }}</a>
                                @else
                                    <span style="font-weight: 600; color: #333; font-size: 14px;">{{ $reply->user?->name ?? 'Pengguna' }}</span>
                                @endif
                                <span style="margin-left: 8px; font-size: 12px; color: #999;">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <p style="margin: 0 0 8px 0; line-height: 1.5; color: #333; font-size: 14px;">{!! $reply->parsed_body !!}</p>
                        <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: #666;">
                            <span>♡ {{ $reply->likes_count ?? 0 }}</span>
                            @auth
                                <button type="button" class="detail-reply-btn"
                                        data-review-id="{{ $review->id }}"
                                        data-parent-id="{{ $reply->id }}"
                                        data-username="{{ $reply->user?->username ?? '' }}"
                                        style="background: none; border: none; color: #666; cursor: pointer; padding: 4px 0; font-weight: 600; font-size: 12px;">Balas</button>
                            @endauth
                        </div>
                    </article>
                @endforeach
            @endforeach

            {{-- Reply form (hidden by default, shown when "Balas" clicked) --}}
            @auth
            <form method="POST" action="/reviews/{{ $review->id }}/comments"
                  class="detail-reply-form"
                  id="reply-form-{{ $review->id }}"
                  style="display:none; margin-left: 40px; margin-top: 16px; padding: 12px 0; border-top: 1px solid rgba(255,255,255,0.1); border-bottom: 1px solid rgba(255,255,255,0.1);">
                @csrf
                <input type="hidden" name="parent_id" id="parent-id-{{ $review->id }}" value="">
                <textarea name="body"
                          id="body-{{ $review->id }}"
                          class="form-textarea"
                          placeholder="Tulis balasan..."
                          rows="2"
                          required
                          style="width: 100%; padding: 10px 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 20px; font-size: 14px; resize: none; line-height: 1.5; outline: none; background: rgba(255,255,255,0.05); color: #fff; transition: border-color 0.2s;"
                          onfocus="this.style.borderColor='rgba(255,255,255,0.45)';"
                          onblur="this.style.borderColor='rgba(255,255,255,0.15)';"></textarea>
                <div style="margin-top: 8px; display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                    <button type="button" class="detail-reply-cancel" data-review-id="{{ $review->id }}"
                            style="padding: 6px 14px; font-size: 13px; background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.6); font-weight: 600; transition: color 0.2s;"
                            onmouseover="this.style.color='#fff'"
                            onmouseout="this.style.color='rgba(255,255,255,0.6)'">Batal</button>
                    <button type="submit" class="user-action-btn"
                            style="padding: 6px 16px; font-size: 13px; background: #4CAF50; border: none; border-radius: 20px; cursor: pointer; color: white; font-weight: 600; transition: opacity 0.2s;"
                            onmouseover="this.style.opacity='0.9'"
                            onmouseout="this.style.opacity='1'">Kirim</button>
                </div>
            </form>
            @endauth
        @empty
            <div class="diskusi-empty">
                <p>Belum ada review untuk film ini.</p>
                @auth
                    <p style="margin-top:8px;">
                        <a href="{{ route('movies.show', $movieId) }}?tab=info#review-form" class="user-action-login-prompt">
                            Jadilah yang pertama menulis review →
                        </a>
                    </p>
                @else
                    <p style="margin-top:8px;">
                        <a href="{{ route('login') }}" class="user-action-login-prompt">
                            Masuk untuk menulis review →
                        </a>
                    </p>
                @endauth
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($communityReviews && $communityReviews->hasPages())
        <div style="margin-top:24px;">
            {{ $communityReviews->links() }}
        </div>
    @endif
</div>

{{-- JavaScript untuk handle Balas button functionality --}}
@auth
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle "Balas" button click
    document.addEventListener('click', function(e) {
        const replyBtn = e.target.closest('.detail-reply-btn');
        if (replyBtn) {
            e.preventDefault();

            const reviewId = replyBtn.dataset.reviewId;
            const parentId = replyBtn.dataset.parentId || '';
            const username = replyBtn.dataset.username || '';

            // Show form
            const form = document.getElementById(`reply-form-${reviewId}`);
            if (form) {
                form.style.display = 'block';

                // Set parent_id
                const parentInput = document.getElementById(`parent-id-${reviewId}`);
                if (parentInput) {
                    parentInput.value = parentId;
                }

                // Set textarea value with @username
                const textarea = document.getElementById(`body-${reviewId}`);
                if (textarea && username) {
                    textarea.value = `@${username} `;
                    textarea.focus();
                    // Move cursor to end
                    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                } else if (textarea) {
                    textarea.focus();
                }

                // Scroll to form
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Handle "Batal" button click
        const cancelBtn = e.target.closest('.detail-reply-cancel');
        if (cancelBtn) {
            e.preventDefault();

            const reviewId = cancelBtn.dataset.reviewId;
            const form = document.getElementById(`reply-form-${reviewId}`);
            if (form) {
                form.style.display = 'none';

                // Reset form
                const textarea = form.querySelector('textarea');
                const parentInput = form.querySelector('input[name="parent_id"]');
                if (textarea) textarea.value = '';
                if (parentInput) parentInput.value = '';
            }
        }
    });
});
</script>
@endauth
