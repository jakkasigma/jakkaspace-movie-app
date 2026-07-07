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
                            <button onclick="document.getElementById('plus-modal').classList.add('active')" class="plus-info-btn" title="Tentang Plus/Plus+">ⓘ</button>
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

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>

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
                <h2 style="color:#fff;font-size:1.1rem;font-weight:700;letter-spacing:0.5px;">
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
                    <a href="{{ route('plus') }}" class="plus-cta-btn" style="display:inline-block;background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:0.85rem;font-weight:700;cursor:pointer;text-decoration:none;">Lihat Harga & Paket</a>
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
                <a href="{{ route('plus') }}" style="color:rgba(255,255,255,0.4);font-size:0.78rem;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Detail lengkap →</a>
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
        background: none;
        border: none;
        color: rgba(255,255,255,0.25);
        font-size: 0.9rem;
        cursor: pointer;
        padding: 0;
        margin-left: 6px;
        vertical-align: middle;
        transition: color 0.2s;
    }
    .plus-info-btn:hover {
        color: rgba(255, 255, 255, 0.6);
    }
    .plus-modal-overlay {
        display: none;
    }
    .plus-modal-overlay.active {
        display: flex !important;
    }
    </style>
@endsection
