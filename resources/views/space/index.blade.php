@extends('layouts.movie')

@section('title', 'Your Space — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <div class="space-identity">
                    <x-user-avatar :user="$user" class="space-avatar" placeholder-class="space-avatar space-avatar-placeholder" />
                    <div>
                        <h1 class="space-name">
                            {{ $user->name }}
                            @if ($user->isPlusPlus())
                                <span class="plus-indicator" onclick="document.getElementById('plus-modal').classList.add('active')" style="cursor:pointer;background:linear-gradient(135deg,#f5af19,#f12711);">Plus+</span>
                            @elseif ($user->isPlus())
                                <span class="plus-indicator" onclick="document.getElementById('plus-modal').classList.add('active')" style="cursor:pointer;">Plus</span>
                            @endif
                            <button onclick="document.getElementById('plus-modal').classList.add('active')" class="plus-info-btn plus-info-trigger" title="Tentang Plus/Plus+">Info Plus</button>
                        </h1>
                        @if ($user->username)
                            <p class="space-username">{{ '@' . $user->username }}</p>
                        @endif
                    </div>
                </div>

                <div class="space-stats">
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['total_watched'] }}</span>
                        <span class="space-stat-label">Ditonton</span>
                    </div>
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['estimated_hours'] }}</span>
                        <span class="space-stat-label">Jam</span>
                    </div>
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['total_diary'] }}</span>
                        <span class="space-stat-label">Diary</span>
                    </div>
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['total_reviews'] }}</span>
                        <span class="space-stat-label">Review</span>
                    </div>
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['total_watchlist'] }}</span>
                        <span class="space-stat-label">Watchlist</span>
                    </div>
                    <div class="space-stat">
                        <span class="space-stat-value">{{ $stats['total_favorites'] }}</span>
                        <span class="space-stat-label">Favorit</span>
                    </div>
                </div>
            </div>
        </header>

        <x-space.nav active="index" />
        <x-space.tab-bar active="index" />

        <div class="space-body">
            {{-- Recently Watched --}}
            <section class="space-section">
                <div class="space-section-header">
                    <h2 class="space-section-title">Terakhir Ditonton</h2>
                    <a href="{{ route('your-space.history') }}" class="space-section-link">Lihat semua</a>
                </div>

                @if (empty($recentWatched))
                    <x-space.empty icon="clock" message="Belum ada film yang ditonton." :link="route('movies.index')" linkText="Mulai temukan film" />
                @else
                    <div class="movie-row">
                        @foreach ($recentWatched as $movie)
                            <x-movie.card :movie="$movie" />
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Recent Diary --}}
            <section class="space-section">
                <div class="space-section-header">
                    <h2 class="space-section-title">Diary Terbaru</h2>
                    <a href="{{ route('your-space.diary') }}" class="space-section-link">Lihat semua</a>
                </div>

                @if ($recentDiary->isEmpty())
                    <x-space.empty icon="book" message="Belum ada diary." :link="route('movies.index')" linkText="Tonton film dan catat diaries" />
                @else
                    <div class="diary-mini-list">
                        @foreach ($recentDiary as $entry)
                            <div class="diary-mini-card">
                                @if ($entry->movie_poster_url)
                                    <img src="{{ $entry->movie_poster_url }}" alt="" class="diary-mini-poster" loading="lazy">
                                @else
                                    <div class="diary-mini-poster diary-mini-poster-empty"></div>
                                @endif
                                <div class="diary-mini-body">
                                    <div class="diary-mini-header">
                                        <span class="diary-mini-title">{{ $entry->movie_title }}</span>
                                        @if ($entry->movie_release_year)
                                            <span class="diary-mini-year">{{ $entry->movie_release_year }}</span>
                                        @endif
                                    </div>
                                    <div class="diary-mini-meta">
                                        <span class="diary-mini-date">{{ $entry->watched_at->locale('id')->translatedFormat('d M Y') }}</span>
                                        @if ($entry->user_rating)
                                            <span class="diary-mini-rating">{{ str_repeat('★', min($entry->user_rating, 5)) }}{{ str_repeat('☆', max(0, 5 - min($entry->user_rating, 5))) }}</span>
                                        @endif
                                        @if ($entry->mood)
                                            <span class="diary-mini-mood">{{ $entry->mood }}</span>
                                        @endif
                                        @if ($entry->is_rewatch)
                                            <span class="diary-mini-badge">Rewatch</span>
                                        @endif
                                    </div>
                                    @if ($entry->notes)
                                        <p class="diary-mini-notes">{{ Str::limit($entry->notes, 120) }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Recent Reviews --}}
            <section class="space-section">
                <div class="space-section-header">
                    <h2 class="space-section-title">Review Terbaru</h2>
                    <a href="{{ route('your-space.diary') }}" class="space-section-link">Lihat semua</a>
                </div>

                @if ($recentReviews->isEmpty())
                    <x-space.empty icon="book" message="Belum ada review." :link="route('movies.index')" linkText="Temukan film dan tulis review" />
                @else
                    <div class="review-mini-list">
                        @foreach ($recentReviews as $review)
                            <div class="review-mini-card">
                                @if ($review->movie_poster_url)
                                    <img src="{{ $review->movie_poster_url }}" alt="" class="review-mini-poster" loading="lazy">
                                @else
                                    <div class="review-mini-poster review-mini-poster-empty"></div>
                                @endif
                                <div class="review-mini-body">
                                    <div class="review-mini-header">
                                        <a href="{{ route('movies.show', $review->tmdb_id) }}" class="review-mini-title">{{ $review->movie_title }}</a>
                                        @if ($review->rating)
                                            <span class="review-mini-rating">{{ str_repeat('★', min($review->rating, 5)) }}{{ str_repeat('☆', max(0, 5 - min($review->rating, 5))) }}</span>
                                        @endif
                                    </div>
                                    @if ($review->body)
                                        <p class="review-mini-text">&ldquo;{{ Str::limit($review->body, 200) }}&rdquo;</p>
                                    @endif
                                    <span class="review-mini-date">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Watchlist Preview --}}
            <section class="space-section">
                <div class="space-section-header">
                    <h2 class="space-section-title">Watchlist</h2>
                    <a href="{{ route('your-space.watchlist') }}" class="space-section-link">Lihat semua</a>
                </div>

                @if (empty($watchlistMovies))
                    <x-space.empty icon="film" message="Watchlist kosong." :link="route('movies.discover')" linkText="Temukan film" />
                @else
                    <div class="movie-row">
                        @foreach ($watchlistMovies as $movie)
                            <x-movie.card :movie="$movie" />
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </main>

    {{-- Promo Popup --}}
    @if (! empty($promoPopup))
        <div id="spacePromoPopup" class="plus-modal-overlay active" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="plus-modal" style="max-width:380px;text-align:center;" onclick="event.stopPropagation()">
                <div style="display:flex;justify-content:flex-end;margin-bottom:4px;">
                    <button onclick="document.getElementById('spacePromoPopup').classList.remove('active'); fetch('{{ route('plus.promo.dismiss') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});" style="background:none;border:none;color:rgba(255,255,255,0.3);font-size:1.3rem;cursor:pointer;padding:0 4px;line-height:1;">✕</button>
                </div>
                <div style="font-size:2.5rem;margin-bottom:12px;">🎉</div>
                <h2 style="color:#fff;font-size:1.1rem;font-weight:700;margin:0 0 8px;">{{ $promoPopup->popup_title ?? $promoPopup->name }}</h2>
                <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;margin:0 0 16px;line-height:1.5;">
                    {{ $promoPopup->popup_message ?? ('Dapatkan diskon ' . ($promoPopup->type === 'percent' ? "{$promoPopup->value}%" : 'Rp' . number_format($promoPopup->value, 0, ',', '.')) . ' untuk semua paket!') }}
                </p>
                <p style="color:rgba(64,224,208,0.8);font-size:0.82rem;margin:0 0 16px;">✨ Diskon otomatis — langsung terlihat di harga</p>
                <a href="{{ route('plus') }}#plans" onclick="document.getElementById('spacePromoPopup').classList.remove('active')" class="plus-cta-btn" style="display:inline-block;font-size:0.85rem;text-decoration:none;">→ Lihat Paket Plus</a>
            </div>
        </div>
    @endif

    {{-- Plus Info Modal --}}
    <div id="plus-modal" class="plus-modal-overlay" style="display:none" onclick="if(event.target===this)this.classList.remove('active')">
        <div class="plus-modal plus-modal--info" onclick="event.stopPropagation()">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h2 style="color:#fff;font-size:1.3rem;font-family:'Bebas Neue',sans-serif;text-transform:uppercase;letter-spacing:1px;">
                    <span class="plus-brand-text" style="background:linear-gradient(135deg,#f5af19,#f12711);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">JAKKA PLUS</span>
                </h2>
                <button onclick="document.getElementById('plus-modal').classList.remove('active')" style="background:none;border:none;color:rgba(255,255,255,0.4);font-size:1.3rem;cursor:pointer;padding:4px;">✕</button>
            </div>

            @if ($user->isPlusPlus())
                {{-- Plus+ User: status + benefits --}}
                <div style="text-align:center;padding:12px 0 20px;border-bottom:1px solid rgba(255,255,255,0.06);margin-bottom:20px;">
                    <span style="display:inline-block;background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;font-size:0.72rem;font-weight:700;padding:3px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">Plus+ Active</span>
                    <p style="color:rgba(255,255,255,0.6);font-size:0.85rem;">Subscription Plus+ aktif sampai <strong style="color:#fff;">{{ $user->expires_at->format('d M Y') }}</strong></p>
                    @php $daysLeft = now()->diffInDays($user->expires_at, false); @endphp
                    @if ($daysLeft > 0 && $daysLeft <= 30)
                        <p style="color:#f5af19;font-size:0.8rem;margin-top:6px;">⏳ {{ $daysLeft }} hari lagi — <a href="{{ route('plus') }}" style="color:#fff;text-decoration:underline;">Perpanjang →</a></p>
                    @endif
                </div>
            @elseif ($user->isPlus())
                {{-- Plus User: status + benefits --}}
                <div style="text-align:center;padding:12px 0 20px;border-bottom:1px solid rgba(255,255,255,0.06);margin-bottom:20px;">
                    <span style="display:inline-block;background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;font-size:0.72rem;font-weight:700;padding:3px 12px;border-radius:999px;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">Plus Active</span>
                    <p style="color:rgba(255,255,255,0.6);font-size:0.85rem;">Subscription aktif sampai <strong style="color:#fff;">{{ $user->expires_at->format('d M Y') }}</strong></p>
                    @php $daysLeft = now()->diffInDays($user->expires_at, false); @endphp
                    @if ($daysLeft > 0 && $daysLeft <= 30)
                        <p style="color:#f5af19;font-size:0.8rem;margin-top:6px;">⏳ {{ $daysLeft }} hari lagi — <a href="{{ route('plus') }}" style="color:#fff;text-decoration:underline;">Perpanjang →</a></p>
                    @endif
                </div>
            @else
                {{-- Free User: upgrade CTA --}}
                <div style="text-align:center;padding:16px 0 24px;border-bottom:1px solid rgba(255,255,255,0.06);margin-bottom:20px;">
                    <p style="color:rgba(255,255,255,0.7);font-size:0.9rem;margin-bottom:12px;">Upgrade ke Plus atau Plus+ dan dapatkan fitur eksklusif!</p>
                    <a href="{{ route('plus') }}" class="plus-cta-btn" style="display:inline-block;background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:0.85rem;font-family:'Bebas Neue',sans-serif;text-transform:uppercase;letter-spacing:0.5px;cursor:pointer;text-decoration:none;">Lihat Harga & Paket</a>
                </div>
            @endif

            {{-- Benefits grid --}}
            <div class="plus-benefits-grid" style="grid-template-columns:1fr 1fr;gap:10px;">
                <div class="plus-benefit-card" style="padding:14px;">
                    <span style="font-size:1.4rem;display:block;margin-bottom:6px;">🎨</span>
                    <h3 style="font-size:0.8rem;font-weight:600;color:#fff;margin:0 0 4px;">Theme Pack</h3>
                    <p style="font-size:0.72rem;color:rgba(255,255,255,0.5);margin:0;line-height:1.4;">Avatar border, aksen warna, & badge eksklusif</p>
                </div>
                <div class="plus-benefit-card" style="padding:14px;">
                    <span style="font-size:1.4rem;display:block;margin-bottom:6px;">📊</span>
                    <h3 style="font-size:0.8rem;font-weight:600;color:#fff;margin:0 0 4px;">Analytics</h3>
                    <p style="font-size:0.72rem;color:rgba(255,255,255,0.5);margin:0;line-height:1.4;">Statistik lanjutan, streak, rating distribution</p>
                </div>
                <div class="plus-benefit-card" style="padding:14px;">
                    <span style="font-size:1.4rem;display:block;margin-bottom:6px;">📥</span>
                    <h3 style="font-size:0.8rem;font-weight:600;color:#fff;margin:0 0 4px;">Export CSV</h3>
                    <p style="font-size:0.72rem;color:rgba(255,255,255,0.5);margin:0;line-height:1.4;">Download diary, review, & watch history</p>
                </div>
                <div class="plus-benefit-card" style="padding:14px;">
                    <span style="font-size:1.4rem;display:block;margin-bottom:6px;">📌</span>
                    <h3 style="font-size:0.8rem;font-weight:600;color:#fff;margin:0 0 4px;">Unlimited</h3>
                    <p style="font-size:0.72rem;color:rgba(255,255,255,0.5);margin:0;line-height:1.4;">Movie lists & pinned movies tanpa batas</p>
                </div>
            </div>

            <div style="text-align:center;margin-top:16px;">
                <a href="{{ route('plus') }}" style="color:rgba(255,255,255,0.4);font-size:0.78rem;font-family:'Bebas Neue',sans-serif;text-transform:uppercase;letter-spacing:0.5px;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Detail lengkap →</a>
            </div>
        </div>
    </div>

    <style>
    .plus-modal--info {
        max-width: 400px !important;
        padding: 28px !important;
        max-height: 80vh;
        overflow-y: auto;
    }
    .plus-modal--info .plus-benefit-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 10px;
        transition: background 0.2s;
    }
    .plus-modal--info .plus-benefit-card:hover {
        background: rgba(255,255,255,0.07);
    }
    .plus-info-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 0.78rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 0.3rem 0.9rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: transparent;
        color: #fff;
        cursor: pointer;
        margin-left: 6px;
        vertical-align: middle;
        transition: background 0.25s ease, transform 0.2s ease;
    }
    .plus-info-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }
    .plus-modal-overlay {
        display: none;
    }
    .plus-modal-overlay.active {
        display: flex !important;
    }
    </style>

@if ($canLinkGoogle)
<div id="google-link-modal" class="glink-overlay">
    <div class="glink-modal" onclick="event.stopPropagation()">
        <button class="glink-close" onclick="dismissGlink(true)" aria-label="Tutup">✕</button>
        <div class="glink-body">
            <div class="glink-logo">
                <svg viewBox="0 0 48 48" width="40" height="40">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59A14.5 14.5 0 0 1 9.5 24c0-1.59.28-3.14.76-4.59l-7.98-6.19A23.99 23.99 0 0 0 0 24c0 3.77.87 7.35 2.56 10.56l7.97-5.97z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 5.97C6.51 42.62 14.62 48 24 48z"/>
                </svg>
            </div>
            <h2 class="glink-title">Hubungkan Akun Google</h2>
            <p class="glink-sub">Masuk lebih cepat tanpa perlu ingat password</p>
            <div class="glink-email">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <span>{{ $user->email }}</span>
            </div>
            <a href="{{ route('auth.google') }}" class="glink-btn">
                <svg viewBox="0 0 48 48" width="18" height="18">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59A14.5 14.5 0 0 1 9.5 24c0-1.59.28-3.14.76-4.59l-7.98-6.19A23.99 23.99 0 0 0 0 24c0 3.77.87 7.35 2.56 10.56l7.97-5.97z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 5.97C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Hubungkan Google
            </a>
            <ul class="glink-benefits">
                <li>Login tanpa password — cukup satu klik</li>
                <li>Akun tetap aman dan terhubung</li>
                <li>Gunakan akun Google kapan saja</li>
            </ul>
            <div class="glink-actions">
                <button class="glink-action glink-later" onclick="dismissGlink(true)">Nanti</button>
                <button class="glink-action glink-never" onclick="dismissGlink(false)">Jangan tampilkan lagi</button>
            </div>
        </div>
    </div>
</div>
<script>
function dismissGlink(temporary) {
    document.getElementById('google-link-modal').classList.remove('active');
    if (! temporary) {
        fetch('{{ route('your-space.dismiss-google-link') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
    }
}
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('google-link-modal');
    if (el) el.classList.add('active');
});
</script>
@endif

@endsection
