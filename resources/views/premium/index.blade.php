@extends('layouts.movie')

@section('title', 'Plus — Jakka Space')
@section('body-class', 'movie-page')

@push('head')
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
@endpush

@section('body')
    <x-movie.navbar />

    <main class="premium-page">
        @if (session('success'))
            <div class="premium-toast">{{ session('success') }}</div>
        @endif

        @if ($user->isPlus() || $user->isPlusPlus())
            @php $tierLabel = $user->isPlusPlus() ? 'Plus+' : 'Plus'; @endphp
            @php $badgeEmoji = $user->isPlusPlus() ? '💎' : '✨'; @endphp
            {{-- ===== SUBSCRIPTION ACTIVE ===== --}}
            <section class="plus-hero plus-hero--active">
                <div style="display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;">
                    <div class="plus-hero-badge" @if($user->isPlusPlus()) style="background:linear-gradient(135deg,#f5af19,#f12711);" @endif>{{ $badgeEmoji }} {{ $tierLabel }} Active</div>
                    <button onclick="document.getElementById('plus-info-modal').classList.add('active')" class="plus-info-trigger">Info {{ $tierLabel }}</button>
                </div>
                <h1 class="plus-hero-title">Subscription {{ $tierLabel }} Aktif</h1>
                <p class="plus-hero-sub">Langganan aktif sampai <strong>{{ $user->expires_at->format('d M Y') }}</strong></p>
                @if ($daysLeft > 0)
                    <div class="plus-countdown">
                        <span class="plus-countdown-num">{{ $daysLeft }}</span>
                        <span class="plus-countdown-label">hari lagi</span>
                    </div>
                @else
                    <p class="plus-hero-sub" style="color:#ff4444;">Subscription akan segera berakhir.</p>
                @endif
            </section>

            <div style="text-align:center;margin-bottom:20px;">
                <a href="{{ route('plus.history') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:0.82rem;text-decoration:none;padding:6px 16px;border:1px solid rgba(255,255,255,0.08);border-radius:6px;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)';this.style.color='var(--muted)'">
                    📜 Riwayat Langganan
                </a>
            </div>

            {{-- Benefits Grid --}}
            <section class="plus-benefits">
                <h2 class="plus-section-title">Fitur Eksklusifmu</h2>
                <div class="plus-benefits-grid">
                    <div class="plus-benefit-card">
                        <span class="plus-benefit-icon">🎨</span>
                        <h3>{{ $user->theme?->name ?? 'Theme Pack' }}</h3>
                        @if ($user->theme)
                            <p>Tema <strong>{{ $user->theme->name }}</strong> aktif — avatar border, aksen warna, & badge {{ $user->theme->badge_icon }}</p>
                        @else
                            <p>Pilih theme pack untuk tampilan eksklusif.</p>
                        @endif
                    </div>
                    <div class="plus-benefit-card">
                        <span class="plus-benefit-icon">📊</span>
                        <h3>Analytics Lanjutan</h3>
                        <p>@if($user->isPlusPlus()) Streak, distribusi rating, per genre/tahun/director. @else Rating distribution, streak, sutradara favorit. @endif</p>
                    </div>
                    <div class="plus-benefit-card">
                        <span class="plus-benefit-icon">📥</span>
                        <h3>Export CSV</h3>
                        <p>@if($user->isPlusPlus()) Batch export semua data. @else Export per halaman. @endif</p>
                    </div>
                    <div class="plus-benefit-card">
                        <span class="plus-benefit-icon">📌</span>
                        <h3>Movie Lists</h3>
                        <p>{{ $user->maxLists() }} list ({{ $user->maxPublicLists() }} publik + {{ $user->maxPrivateLists() }} privat) — {{ $user->maxMoviesPerList() === -1 ? 'Unlimited' : $user->maxMoviesPerList() }} film per list.</p>
                    </div>
                </div>
            </section>

            {{-- Renew / Upgrade --}}
            @if ($daysLeft <= 30 || ! $user->isPlusPlus())
                <section class="plus-renew">
                    <h2 class="plus-section-title">🔄 {{ $user->isPlusPlus() ? 'Perpanjang' : ($user->isPlus() ? 'Upgrade ke Plus+' : 'Perpanjang') }} Subscription</h2>
                    @if ($user->isPlusPlus())
                        <p class="plus-renew-hint">Masa aktifmu tinggal <strong>{{ $daysLeft }} hari</strong>. Perpanjang sekarang biar ga kehilangan akses Plus+.</p>
                    @elseif ($user->isPlus())
                        <p class="plus-renew-hint">Kamu Plus! Upgrade ke <strong>Plus+</strong> untuk fitur lebih eksklusif: cover list custom, early access, prioritas support, batch export, & analytics per genre/tahun/director.</p>
                    @else
                        <p class="plus-renew-hint">Masa aktifmu tinggal <strong>{{ $daysLeft }} hari</strong>. Perpanjang sekarang.</p>
                    @endif
                    <div class="plus-plans">
                        @foreach ($plans as $plan)
                            @if ($user->isPlusPlus() && $plan->tier !== 'plus_plus') @continue @endif
                            @if ($user->isPlus() && ! $user->isPlusPlus() && $plan->tier === 'plus') @continue @endif
                            <div class="plus-plan-card @if($plan->is_recommended)plus-plan-featured @endif">
                                @if($plan->is_recommended)<div class="plus-plan-badge">Terbaik</div>@endif
                                <h3 class="plus-plan-name">{{ $plan->name }}</h3>
                                <p class="plus-plan-price">
                                    @if($plan->hasActiveAutoPromo())
                                        <span class="plus-price-original">{{ $plan->priceFormatted() }}</span>
                                        Rp{{ number_format($plan->discountedPrice(), 0, ',', '.') }}<span class="plus-plan-period">/{{ $plan->periodLabel() }}</span>
                                    @else
                                        {{ $plan->priceFormatted() }}<span class="plus-plan-period">/{{ $plan->periodLabel() }}</span>
                                    @endif
                                </p>
                                @if ($plan->duration_days > 30)
                                    <p class="plus-plan-efektif">Rp{{ number_format($plan->price / $plan->duration_days * 30, 0, ',', '.') }}/bulan</p>
                                @endif
                                <ul class="plus-plan-features">
                                    @if ($plan->tier === 'plus')
                                        <li>7 movie lists</li>
                                        <li>100 film per list</li>
                                        <li>Theme packs + badge 👑</li>
                                        <li>Export CSV per halaman</li>
                                        <li>Analytics lanjutan</li>
                                    @else
                                        <li>15 movie lists</li>
                                        <li>Unlimited film per list</li>
                                        <li>12 pinned movies</li>
                                        <li>Cover list custom</li>
                                        <li>Batch export semua data</li>
                                        <li>Analytics per genre/tahun/director</li>
                                        <li>Early access fitur baru</li>
                                        <li>Prioritas support</li>
                                        <li>Riwayat analytics selamanya</li>
                                    @endif
                                </ul>
                                <button type="button" class="plus-plan-btn @if($plan->is_recommended)plus-plan-btn-primary @endif" data-plan-id="{{ $plan->id }}" data-price="{{ $plan->price }}" onclick="showPayment({{ $plan->id }})">{{ $user->isPlus() || $user->isPlusPlus() ? 'Perpanjang' : 'Langganan' }}</button>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Redeem Code --}}
            <section class="plus-faq" id="redeem" style="margin-bottom:28px;">
                <h2 class="plus-section-title">🎁 Punya Kode Redeem?</h2>
                <div style="max-width:400px;margin:0 auto;">
                    <form method="POST" action="{{ route('plus.redeem') }}" style="display:flex;gap:10px;">
                        @csrf
                        <input type="text" name="code" placeholder="Masukkan kode redeem..." required
                            style="flex:1;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:8px;padding:10px 14px;color:#fff;font-size:0.85rem;outline:none;">
                        <button type="submit"
                            style="background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;border:none;border-radius:8px;padding:10px 20px;font-size:0.82rem;font-weight:600;cursor:pointer;white-space:nowrap;">Tukarkan</button>
                    </form>
                    @if (session('redeem_error'))
                        <p style="color:#ef4444;font-size:0.8rem;margin-top:8px;">{{ session('redeem_error') }}</p>
                    @endif
                    @if (session('redeem_success'))
                        <p style="color:#22c55e;font-size:0.8rem;margin-top:8px;">{{ session('redeem_success') }}</p>
                    @endif
                </div>
            </section>

            {{-- Theme Selector --}}
            <section class="plus-theme-section">
                <h2 class="plus-section-title">🎨 Ganti Theme Pack</h2>
                <p class="plus-section-desc">Pilih tema yang mempengaruhi avatar border, username, review card, & chat bubble di seluruh platform.</p>
                <div class="plus-themes">
                    {{-- Default --}}
                    <form method="POST" action="{{ route('plus.theme') }}" class="plus-theme-card-wrapper">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="theme_id" value="0">
                        <button type="submit" class="plus-theme-card {{ ! $user->theme_id ? 'plus-theme-active' : '' }}" @if (! $user->theme_id) disabled @endif>
                            <div class="plus-theme-avatar-demo" style="--demo-border: linear-gradient(135deg, #555, #333);">
                                <span class="plus-theme-avatar-inner" style="border-color:#444;">J</span>
                            </div>
                            <span class="plus-theme-name">Default</span>
                            <span class="plus-theme-badge">—</span>
                            @if (! $user->theme_id)
                                <span class="plus-theme-check">✓ Aktif</span>
                            @endif
                        </button>
                    </form>
                    @foreach ($themes as $theme)
                        <form method="POST" action="{{ route('plus.theme') }}" class="plus-theme-card-wrapper">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="theme_id" value="{{ $theme->id }}">
                            <button type="submit" class="plus-theme-card {{ $user->theme_id === $theme->id ? 'plus-theme-active' : '' }}" @if ($user->theme_id === $theme->id) disabled @endif>
                                <div class="plus-theme-avatar-demo" style="--demo-border: {{ $theme->avatar_border_css }}">
                                    <span class="plus-theme-avatar-inner">J</span>
                                </div>
                                <span class="plus-theme-name">{{ $theme->name }}</span>
                                <span class="plus-theme-badge">{{ $theme->badge_icon }}</span>
                                @if ($user->theme_id === $theme->id)
                                    <span class="plus-theme-check">✓ Aktif</span>
                                @endif
                            </button>
                        </form>
                    @endforeach
                </div>
            </section>
        @else
            {{-- ===== PRICING PAGE (FREE USER) ===== --}}
            <section class="plus-hero">
                <div class="plus-hero-brand">JAKKA <span class="plus-brand-text">PLUS</span></div>
                <h1 class="plus-hero-title">Tingkatkan Pengalaman <br>Nonton & Catat Filmmu</h1>
                <p class="plus-hero-sub">Dapatkan theme packs eksklusif, unlimited lists, export data, analytics lanjutan, dan badge premium di seluruh platform.</p>
                <div class="plus-hero-cta">
                    <a href="#plans" class="plus-cta-btn">Lihat Paket</a>
                    <a href="#compare" class="plus-cta-btn plus-cta-btn-secondary">Bandingkan Fitur</a>
                </div>
            </section>

            {{-- Comparison Table --}}
            <section class="plus-compare" id="compare">
                <h2 class="plus-section-title">Bandingkan Fitur</h2>
                <table class="plus-table">
                    <thead>
                        <tr>
                            <th>Fitur</th>
                            <th>Free</th>
                            <th>Plus</th>
                            <th>Plus+</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Movie lists</td>
                            <td class="plus-cell-free">1 list</td>
                            <td class="plus-cell-plus">7 list</td>
                            <td class="plus-cell-plus">15 list</td>
                        </tr>
                        <tr>
                            <td>Film per list</td>
                            <td class="plus-cell-free">50</td>
                            <td class="plus-cell-plus">100</td>
                            <td class="plus-cell-plus">Unlimited</td>
                        </tr>
                        <tr>
                            <td>Pinned movies</td>
                            <td class="plus-cell-free">6</td>
                            <td class="plus-cell-plus">6</td>
                            <td class="plus-cell-plus">12</td>
                        </tr>
                        <tr>
                            <td>Export data CSV</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-plus">✅ per halaman</td>
                            <td class="plus-cell-plus">✅ batch</td>
                        </tr>
                        <tr>
                            <td>Analytics lanjutan</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-plus">Streak + distribusi</td>
                            <td class="plus-cell-plus">Streak + distribusi + genre/tahun/director</td>
                        </tr>
                        <tr>
                            <td>Theme packs</td>
                            <td class="plus-cell-free">Default</td>
                            <td class="plus-cell-plus">Bebas pilih</td>
                            <td class="plus-cell-plus">Bebas pilih</td>
                        </tr>
                        <tr>
                            <td>Badge premium</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-plus">👑 Plus</td>
                            <td class="plus-cell-plus">💎 Plus+</td>
                        </tr>
                        <tr>
                            <td>Cover list custom</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-plus">✅</td>
                        </tr>
                        <tr>
                            <td>Early access fitur</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-plus">✅</td>
                        </tr>
                        <tr>
                            <td>Prioritas support</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-plus">✅</td>
                        </tr>
                        <tr>
                            <td>Aksen warna di seluruh platform</td>
                            <td class="plus-cell-free">❌</td>
                            <td class="plus-cell-plus">✅ dasar</td>
                            <td class="plus-cell-plus">✅ lebih ekspresif</td>
                        </tr>
                        <tr>
                            <td>Riwayat analytics</td>
                            <td class="plus-cell-free">1 tahun</td>
                            <td class="plus-cell-plus">3 tahun</td>
                            <td class="plus-cell-plus">Selamanya</td>
                        </tr>
                        <tr>
                            <td>Review character limit</td>
                            <td class="plus-cell-free">5.000</td>
                            <td class="plus-cell-plus">10.000</td>
                            <td class="plus-cell-plus">25.000</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            {{-- Theme Catalog --}}
            <section class="plus-catalog">
                <h2 class="plus-section-title">Katalog Theme Pack</h2>
                <p class="plus-section-desc">Setiap tema memberikan avatar border gradient, aksen warna, dan badge unik yang tampil di profil, timeline, review, chat, dan komentar.</p>
                <div class="plus-themes">
                    @foreach ($themes as $theme)
                        <div class="plus-theme-card-preview">
                            <div class="plus-theme-avatar-demo" style="--demo-border: {{ $theme->avatar_border_css }}">
                                <span class="plus-theme-avatar-inner">J</span>
                            </div>
                            <span class="plus-theme-name">{{ $theme->name }}</span>
                            <span class="plus-theme-badge">{{ $theme->badge_icon }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Plans --}}
            <section class="plus-plans-section" id="plans">
                <h2 class="plus-section-title">Pilih Paketmu</h2>
                <div class="plus-plans">
                    @foreach ($plans as $plan)
                        <div class="plus-plan-card @if($plan->is_recommended)plus-plan-featured @endif">
                            @if($plan->is_recommended)<div class="plus-plan-badge">Terbaik</div>@endif
                            <h3 class="plus-plan-name">{{ $plan->name }}</h3>
                            <p class="plus-plan-price">
                                @if($plan->hasActiveAutoPromo())
                                    <span class="plus-price-original">{{ $plan->priceFormatted() }}</span>
                                    Rp{{ number_format($plan->discountedPrice(), 0, ',', '.') }}<span class="plus-plan-period">/{{ $plan->periodLabel() }}</span>
                                @else
                                    {{ $plan->priceFormatted() }}<span class="plus-plan-period">/{{ $plan->periodLabel() }}</span>
                                @endif
                            </p>
                            @if ($plan->duration_days > 30)
                                <p class="plus-plan-efektif">Rp{{ number_format($plan->price / $plan->duration_days * 30, 0, ',', '.') }}/bulan</p>
                            @endif
                            <ul class="plus-plan-features">
                                @if ($plan->tier === 'plus')
                                    <li>7 movie lists</li>
                                    <li>100 film per list</li>
                                    <li>Theme packs + badge 👑</li>
                                    <li>Export CSV per halaman</li>
                                    <li>Analytics lanjutan</li>
                                @else
                                    <li>15 movie lists</li>
                                    <li>Unlimited film per list</li>
                                    <li>12 pinned movies</li>
                                    <li>Cover list custom</li>
                                    <li>Batch export semua data</li>
                                    <li>Analytics per genre/tahun/director</li>
                                    <li>Early access fitur baru</li>
                                    <li>Prioritas support</li>
                                    <li>Riwayat analytics selamanya</li>
                                @endif
                            </ul>
                            <button type="button" class="plus-plan-btn @if($plan->is_recommended)plus-plan-btn-primary @endif" onclick="showPayment({{ $plan->id }})">Langganan</button>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Redeem Code --}}
            <section class="plus-faq" id="redeem">
                <h2 class="plus-section-title">🎁 Punya Kode Redeem?</h2>
                <div style="max-width:400px;margin:0 auto;">
                    <form method="POST" action="{{ route('plus.redeem') }}" style="display:flex;gap:10px;">
                        @csrf
                        <input type="text" name="code" placeholder="Masukkan kode redeem..." required
                            style="flex:1;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:8px;padding:10px 14px;color:#fff;font-size:0.85rem;outline:none;">
                        <button type="submit"
                            style="background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;border:none;border-radius:8px;padding:10px 20px;font-size:0.82rem;font-weight:600;cursor:pointer;white-space:nowrap;">Tukarkan</button>
                    </form>
                    @if (session('redeem_error'))
                        <p style="color:#ef4444;font-size:0.8rem;margin-top:8px;">{{ session('redeem_error') }}</p>
                    @endif
                    @if (session('redeem_success'))
                        <p style="color:#22c55e;font-size:0.8rem;margin-top:8px;">{{ session('redeem_success') }}</p>
                    @endif
                </div>
            </section>

            <div style="text-align:center;margin-bottom:20px;">
                <a href="{{ route('plus.history') }}" style="display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:0.82rem;text-decoration:none;padding:6px 16px;border:1px solid rgba(255,255,255,0.08);border-radius:6px;transition:all 0.2s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)';this.style.color='var(--muted)'">
                    📜 Riwayat Langganan
                </a>
            </div>

            {{-- FAQ --}}
            <section class="plus-faq">
                <h2 class="plus-section-title">Pertanyaan Umum</h2>
                <div class="plus-faq-list">
                    <details class="plus-faq-item">
                        <summary class="plus-faq-q">Apa perbedaan Plus dan Plus+?</summary>
                        <p class="plus-faq-a"><strong>Plus</strong> (Rp15K/bln): 7 movie lists, 100 film/list, theme packs, export CSV per halaman, analytics streak & distribusi. <strong>Plus+</strong> (Rp30K/bln): 15 lists, unlimited film, 12 pinned movies, cover list custom, batch export, analytics per genre/tahun/director, early access, prioritas support, & riwayat selamanya.</p>
                    </details>
                    <details class="plus-faq-item">
                        <summary class="plus-faq-q">Bisa batalkan subscription?</summary>
                        <p class="plus-faq-a">Ya. Kapan saja dari halaman ini atau hubungi admin.</p>
                    </details>
                    <details class="plus-faq-item">
                        <summary class="plus-faq-q">Apa yang terjadi kalau subscription habis?</summary>
                        <p class="plus-faq-a">Theme pack akan dinonaktifkan, dan fitur premium akan kembali ke batas free. Data kamu tetap aman.</p>
                    </details>
                    <details class="plus-faq-item">
                        <summary class="plus-faq-q">Bagaimana cara pakai kode redeem?</summary>
                        <p class="plus-faq-a">Masukkan kode yang kamu dapatkan di form "Punya Kode Redeem?" di halaman ini. Kode redeem bersifat kumulatif — redeem kode saat sudah Plus akan memperpanjang masa aktif.</p>
                    </details>
                    <details class="plus-faq-item">
                        <summary class="plus-faq-q">Apa itu redeem kode?</summary>
                        <p class="plus-faq-a">Kode redeem adalah kode khusus yang dibagikan admin untuk mendapatkan akses Plus atau Plus+ gratis. Cek form redeem di atas untuk menukarkan kode.</p>
                    </details>
                </div>
            </section>
        @endif

        {{-- Plus Info Modal --}}
        <div id="plus-info-modal" class="plus-modal-overlay" style="display:none" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="plus-modal plus-modal--info" onclick="event.stopPropagation()">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h2 style="color:#fff;font-size:1.3rem;font-family:'Bebas Neue',sans-serif;text-transform:uppercase;letter-spacing:1px;">
                        <span class="plus-brand-text" style="background:linear-gradient(135deg,#f5af19,#f12711);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Info Plus</span>
                    </h2>
                    <button onclick="document.getElementById('plus-info-modal').classList.remove('active')" style="background:none;border:none;color:rgba(255,255,255,0.4);font-size:1.3rem;cursor:pointer;padding:4px;">✕</button>
                </div>

                <h3 style="color:#fff;font-size:0.9rem;font-family:'Bebas Neue',sans-serif;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">Fitur Eksklusif</h3>
                <div class="plus-benefits-grid" style="grid-template-columns:1fr 1fr;gap:10px;margin-bottom:28px;">
                    <div class="plus-benefit-card" style="padding:14px;">
                        <span style="font-size:1.4rem;display:block;margin-bottom:6px;">🎨</span>
                        <h3 style="font-size:0.8rem;font-weight:600;color:#fff;margin:0 0 4px;">Theme Pack</h3>
                        <p style="font-size:0.72rem;color:rgba(255,255,255,0.5);margin:0;line-height:1.4;">Avatar border gradient, aksen warna, badge unik</p>
                    </div>
                    <div class="plus-benefit-card" style="padding:14px;">
                        <span style="font-size:1.4rem;display:block;margin-bottom:6px;">📊</span>
                        <h3 style="font-size:0.8rem;font-weight:600;color:#fff;margin:0 0 4px;">Analytics</h3>
                        <p style="font-size:0.72rem;color:rgba(255,255,255,0.5);margin:0;line-height:1.4;">Streak, rating distribution, sutradara favorit</p>
                    </div>
                    <div class="plus-benefit-card" style="padding:14px;">
                        <span style="font-size:1.4rem;display:block;margin-bottom:6px;">📥</span>
                        <h3 style="font-size:0.8rem;font-weight:600;color:#fff;margin:0 0 4px;">Export CSV</h3>
                        <p style="font-size:0.72rem;color:rgba(255,255,255,0.5);margin:0;line-height:1.4;">Download diary, review, & history kapan saja</p>
                    </div>
                    <div class="plus-benefit-card" style="padding:14px;">
                        <span style="font-size:1.4rem;display:block;margin-bottom:6px;">📌</span>
                        <h3 style="font-size:0.8rem;font-weight:600;color:#fff;margin:0 0 4px;">Unlimited</h3>
                        <p style="font-size:0.72rem;color:rgba(255,255,255,0.5);margin:0;line-height:1.4;">Movie lists & pinned movies tanpa batas</p>
                    </div>
                </div>

                <h3 style="color:#fff;font-size:0.9rem;font-family:'Bebas Neue',sans-serif;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">Bandingkan Fitur</h3>
                <table class="plus-table" style="font-size:0.78rem;">
                    <thead>
                        <tr>
                            <th>Fitur</th>
                            <th>Free</th>
                            <th>Plus</th>
                            <th>Plus+</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>Movie lists</td><td class="plus-cell-free">1 list</td><td class="plus-cell-plus">7</td><td class="plus-cell-plus">15</td></tr>
                        <tr><td>Film per list</td><td class="plus-cell-free">50</td><td class="plus-cell-plus">100</td><td class="plus-cell-plus">∞</td></tr>
                        <tr><td>Pinned movies</td><td class="plus-cell-free">6</td><td class="plus-cell-plus">6</td><td class="plus-cell-plus">12</td></tr>
                        <tr><td>Export CSV</td><td class="plus-cell-free">❌</td><td class="plus-cell-plus">per halaman</td><td class="plus-cell-plus">batch</td></tr>
                        <tr><td>Cover list custom</td><td class="plus-cell-free">❌</td><td class="plus-cell-free">❌</td><td class="plus-cell-plus">✅</td></tr>
                        <tr><td>Early access</td><td class="plus-cell-free">❌</td><td class="plus-cell-free">❌</td><td class="plus-cell-plus">✅</td></tr>
                        <tr><td>Prioritas support</td><td class="plus-cell-free">❌</td><td class="plus-cell-free">❌</td><td class="plus-cell-plus">✅</td></tr>
                        <tr><td>Riwayat analytics</td><td class="plus-cell-free">1 thn</td><td class="plus-cell-plus">3 thn</td><td class="plus-cell-plus">∞</td></tr>
                        <tr><td>Review limit</td><td class="plus-cell-free">5.000</td><td class="plus-cell-plus">10.000</td><td class="plus-cell-plus">25.000</td></tr>
                        <tr><td>Badge & aksen</td><td class="plus-cell-free">❌</td><td class="plus-cell-plus">👑</td><td class="plus-cell-plus">💎</td></tr>
                    </tbody>
                </table>

                <div style="text-align:center;margin-top:20px;">
                    @if ($user->isPlusPlus())
                        <p style="color:rgba(255,255,255,0.4);font-size:0.8rem;">Kamu sudah menikmati semua fitur Plus+ 💎</p>
                    @elseif ($user->isPlus())
                        <p style="color:rgba(255,255,255,0.4);font-size:0.8rem;">Kamu Plus! Upgrade ke Plus+ untuk fitur lebih eksklusif.</p>
                        <a href="#plans" class="plus-cta-btn" style="display:inline-block;background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:0.9rem;font-family:'Bebas Neue',sans-serif;text-transform:uppercase;letter-spacing:0.5px;cursor:pointer;text-decoration:none;">Upgrade ke Plus+</a>
                    @else
                        <a href="#plans" class="plus-cta-btn" style="display:inline-block;background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:0.9rem;font-family:'Bebas Neue',sans-serif;text-transform:uppercase;letter-spacing:0.5px;cursor:pointer;text-decoration:none;">Upgrade Sekarang</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Success Modal (subscription / redeem) --}}
        @php
            $result = session('subscription_result') ?? session('redeem_result');
        @endphp
        @if ($result)
            @php
                $tierLabel = $result['tier'] === 'plus_plus' ? 'Plus+' : 'Plus';
                $icon = $result['action'] === 'upgrade' ? '✨' : ($result['action'] === 'renew' ? '✅' : '🎉');
                if ($result['action'] === 'subscribe') {
                    $title = "🎉 Selamat! Kamu sekarang {$tierLabel}!";
                } elseif ($result['action'] === 'renew') {
                    $title = "✅ Subscription {$tierLabel} Diperpanjang";
                } else {
                    $title = "✨ Upgrade ke {$tierLabel} Berhasil!";
                }
            @endphp
            <div id="successModal" class="plus-modal-overlay active">
                <div class="plus-modal plus-modal--info" onclick="event.stopPropagation()">
                    <div style="text-align:center;padding:8px 0 16px;">
                        <div style="font-size:2.2rem;margin-bottom:8px;">{{ $icon }}</div>
                        <h2 style="color:#fff;font-size:1.1rem;font-weight:700;margin:0;">{{ $title }}</h2>
                    </div>

                    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:16px;margin-bottom:16px;">
                        @if ($result['action'] === 'subscribe')
                            <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;margin:0 0 8px;">
                                Kamu mendapatkan akses <strong style="color:#fff;">{{ $tierLabel }}</strong> selama <strong style="color:#fff;">{{ $result['total_days'] }} hari</strong>.
                            </p>
                        @elseif ($result['action'] === 'renew')
                            <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;margin:0 0 8px;">
                                Subscription {{ $tierLabel }} diperpanjang. Total <strong style="color:#fff;">{{ $result['total_days'] }} hari</strong> masa aktif.
                            </p>
                        @elseif ($result['action'] === 'upgrade')
                            <div style="font-size:0.85rem;color:rgba(255,255,255,0.7);">
                                <p style="margin:0 0 8px;">Sisa langganan <strong>Plus</strong>mu &nbsp;<span style="color:#fff;font-weight:600;">{{ $result['remaining_days'] }} hari</span></p>
                                <div style="display:flex;align-items:center;gap:8px;margin:6px 0;padding:6px 10px;background:rgba(255,255,255,0.04);border-radius:6px;">
                                    <span style="color:#f5af19;font-weight:600;">Konversi 2:1</span>
                                    <span style="color:rgba(255,255,255,0.3);">→</span>
                                    <span style="color:#fff;font-weight:600;">{{ $result['converted_days'] }} hari Plus+</span>
                                </div>
                                <p style="margin:4px 0 0;">+ Kode redeem <strong style="color:#fff;">{{ $result['plan_days'] ?? $result['code_days'] }} hari</strong></p>
                                <div style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(255,255,255,0.06);">
                                    <span style="color:rgba(255,255,255,0.4);">Total: </span>
                                    <span style="color:#fff;font-weight:700;font-size:1rem;">{{ $result['total_days'] }} hari {{ $tierLabel }}</span>
                                </div>
                            </div>
                        @endif
                        <p style="color:rgba(255,255,255,0.4);font-size:0.78rem;margin:10px 0 0;">
                            Aktif sampai <strong style="color:#fff;">{{ $result['expires_at'] }}</strong>
                        </p>
                    </div>

                    <button onclick="document.getElementById('successModal').classList.remove('active')" class="plus-cta-btn" style="display:block;width:100%;background:linear-gradient(135deg,#f5af19,#f12711);color:#fff;border:none;border-radius:8px;padding:10px;font-size:0.9rem;font-family:'Bebas Neue',sans-serif;text-transform:uppercase;letter-spacing:0.5px;cursor:pointer;text-align:center;">Mengerti</button>
                </div>
            </div>
        @endif

        {{-- Payment Modal --}}
        <div class="plus-modal-overlay" id="paymentModal" style="display:none" onclick="if(event.target===this)hidePayment()">
            <div class="plus-modal">
                <h2 class="plus-modal-title">Konfirmasi Pembayaran</h2>

                <div id="paymentSummary" style="margin-bottom:16px;padding:12px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:8px;">
                    <p style="font-size:0.78rem;color:var(--muted);margin:0 0 4px;">Paket dipilih</p>
                    <p id="paymentPlanName" style="font-size:1rem;font-weight:600;color:#fff;margin:0;"></p>
                    <p id="paymentTotal" style="font-size:1.6rem;font-family:'Bebas Neue',sans-serif;color:#a3e635;margin:8px 0 0;"></p>
                </div>

                {{-- Promo Section --}}
                <div style="margin-bottom:16px;padding:12px;border:1px solid rgba(255,255,255,0.08);border-radius:8px;background:rgba(255,255,255,0.02);">
                    <p style="font-size:0.78rem;color:var(--muted);margin:0 0 8px;font-weight:600;">Promo</p>
                    <div style="display:flex;gap:8px;">
                        <input type="text" id="promoCodeField" placeholder="Kode promo..." style="flex:1;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:6px;padding:8px 10px;color:#fff;font-size:0.82rem;outline:none;">
                        <button type="button" id="applyPromoBtn" onclick="applyPromo()"
                            style="background:var(--accent);color:#000;border:none;border-radius:6px;padding:8px 14px;font-size:0.78rem;font-weight:600;cursor:pointer;white-space:nowrap;">Pakai</button>
                    </div>
                    <div id="promoResult" style="margin-top:8px;font-size:0.78rem;"></div>
                </div>

                <p style="font-size:0.72rem;color:var(--text-muted);margin-bottom:16px;">Pembayaran diproses via Midtrans (GoPay, QRIS, Transfer Bank, Virtual Account, dll).</p>

                <button type="button" class="plus-modal-submit" id="payBtn" onclick="processPayment()">Bayar</button>
                <button type="button" class="plus-modal-cancel" onclick="hidePayment()">Batal</button>
            </div>
        </div>

        <input type="hidden" id="selectedPlan" value="">
    </main>

    <style>
    .plus-modal--info {
        max-width: 420px !important;
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
    .plus-modal--info .plus-table {
        font-size: 0.78rem;
    }
    .plus-modal-overlay {
        display: none;
    }
    .plus-modal-overlay.active {
        display: flex !important;
    }
    .plus-price-original {
        text-decoration: line-through;
        color: rgba(255,255,255,0.4);
        font-size: 0.85rem;
        margin-right: 6px;
    }
    .promo-label {
        display: inline-block;
        font-size: 0.72rem;
        padding: 3px 8px;
        border-radius: 4px;
        background: rgba(64,224,208,0.15);
        color: var(--accent);
        margin-top: 4px;
    }
    </style>
    <script>
        let currentPlanId = null;
        let planData = {};

        function showPayment(planId) {
            currentPlanId = planId;
            document.getElementById('selectedPlan').value = planId;
            document.getElementById('promoCodeField').value = '';
            document.getElementById('promoResult').innerHTML = '';
            document.getElementById('paymentModal').style.display = 'flex';

            // Show plan info in modal
            const card = document.querySelector(`[data-plan-id="${planId}"]`);
            if (card) {
                const name = card.querySelector('.plus-plan-name')?.textContent || 'Paket';
                const price = card.dataset.price || '0';
                document.getElementById('paymentPlanName').textContent = name;
                document.getElementById('paymentTotal').textContent = 'Rp ' + parseInt(price).toLocaleString('id-ID');
            }
        }

        function hidePayment() {
            document.getElementById('paymentModal').style.display = 'none';
            currentPlanId = null;
        }

        function processPayment() {
            const btn = document.getElementById('payBtn');
            btn.disabled = true;
            btn.textContent = 'Memproses...';

            fetch('{{ route('plus.subscribe') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    plan_id: currentPlanId
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.snap_token) {
                    hidePayment();
                    let paid = false;
                    snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            paid = true;
                            window.location.replace('{{ route('plus.finish') }}?order_id=' + result.order_id + '&transaction_status=settlement');
                        },
                        onPending: function(result) {
                            window.location.replace('{{ route('plus.finish') }}?order_id=' + result.order_id + '&transaction_status=pending');
                        },
                        onError: function(result) {
                            paid = true;
                            window.location.replace('{{ route('plus.finish') }}?order_id=' + result.order_id + '&transaction_status=error');
                        },
                        onClose: function() {
                            if (!paid) {
                                btn.disabled = false;
                                btn.textContent = 'Bayar';
                            }
                        }
                    });
                } else {
                    alert(data.error || 'Gagal memproses pembayaran.');
                    btn.disabled = false;
                    btn.textContent = 'Bayar';
                }
            })
            .catch(() => {
                alert('Terjadi kesalahan. Silakan coba lagi.');
                btn.disabled = false;
                btn.textContent = 'Bayar';
            });
        }

        function applyPromo() {
            const code = document.getElementById('promoCodeField').value.trim();
            const resultDiv = document.getElementById('promoResult');
            const btn = document.getElementById('applyPromoBtn');
            if (!code) return;
            if (!currentPlanId) {
                resultDiv.innerHTML = '<span style="color:#ef4444;">Pilih paket terlebih dahulu.</span>';
                return;
            }

            btn.disabled = true;
            btn.textContent = '...';
            resultDiv.innerHTML = '<span style="color:var(--muted);">Memvalidasi...</span>';

            fetch('{{ route('plus.promo.validate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ code, plan_id: currentPlanId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.valid) {
                    resultDiv.innerHTML = `
                        <div style="color:#4caf50;font-size:0.82rem;">
                            Diskon: ${data.discount_label} → -Rp${(data.original_price - data.discounted_price).toLocaleString('id-ID')}
                        </div>
                        <div style="color:#fff;font-size:0.9rem;font-weight:700;margin-top:4px;">
                            Total: Rp${data.discounted_price.toLocaleString('id-ID')}
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<span style="color:#ef4444;font-size:0.82rem;">${data.error}</span>`;
                }
            })
            .catch(() => {
                resultDiv.innerHTML = '<span style="color:#ef4444;font-size:0.82rem;">Gagal validasi. Coba lagi.</span>';
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Pakai';
            });
        }
    </script>

    {{-- Promo Popup (dari SubscriptionPromo) --}}
    @php $hasPromoPopup = isset($promoPopup) && ! empty($promoPopup) && ! $user->isPlus() && ! session('promo_popup_dismissed', false); @endphp
    @if ($hasPromoPopup)
        <div id="plusPromoPopup" class="plus-modal-overlay active" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="plus-modal" style="max-width:380px;text-align:center;" onclick="event.stopPropagation()">
                <div style="display:flex;justify-content:flex-end;margin-bottom:4px;">
                    <button onclick="document.getElementById('plusPromoPopup').classList.remove('active'); fetch('{{ route('plus.promo.dismiss') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});" style="background:none;border:none;color:rgba(255,255,255,0.3);font-size:1.3rem;cursor:pointer;padding:0 4px;line-height:1;">✕</button>
                </div>
                <div style="font-size:2.5rem;margin-bottom:12px;">🎉</div>
                <h2 style="color:#fff;font-size:1.1rem;font-weight:700;margin:0 0 8px;">{{ $promoPopup->popup_title ?? $promoPopup->name }}</h2>
                <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;margin:0 0 16px;line-height:1.5;">
                    {{ $promoPopup->popup_message ?? ('Dapatkan diskon ' . ($promoPopup->type === 'percent' ? "{$promoPopup->value}%" : 'Rp' . number_format($promoPopup->value, 0, ',', '.')) . ' untuk semua paket!') }}
                </p>
                <p style="color:rgba(64,224,208,0.8);font-size:0.82rem;margin:0 0 16px;">✨ Diskon otomatis — langsung terlihat di harga</p>
                <a href="{{ route('plus') }}#plans" onclick="document.getElementById('plusPromoPopup').classList.remove('active')" class="plus-cta-btn" style="display:inline-block;font-size:0.85rem;text-decoration:none;">→ Lihat Paket Plus</a>
            </div>
        </div>
    @endif

    {{-- Redeem Promo Popup (dari session redeem_promo) --}}
    @if (! empty($redeemPromo))
        <div id="redeemPromoPopup" class="plus-modal-overlay active" onclick="if(event.target===this)this.classList.remove('active')">
            <div class="plus-modal" style="max-width:380px;text-align:center;" onclick="event.stopPropagation()">
                <div style="display:flex;justify-content:flex-end;margin-bottom:4px;">
                    <button onclick="document.getElementById('redeemPromoPopup').classList.remove('active'); fetch('{{ route('plus.promo.dismiss') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});" style="background:none;border:none;color:rgba(255,255,255,0.3);font-size:1.3rem;cursor:pointer;padding:0 4px;line-height:1;">✕</button>
                </div>
                <div style="font-size:2.5rem;margin-bottom:12px;">🎉</div>
                <h2 style="color:#fff;font-size:1.1rem;font-weight:700;margin:0 0 8px;">{{ $redeemPromo['popup_title'] ?? 'Kode Promo Berhasil!' }}</h2>
                <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;margin:0 0 16px;line-height:1.5;">
                    {{ $redeemPromo['popup_message'] ?? ('Dapatkan diskon ' . ($redeemPromo['discount_type'] === 'percent' ? "{$redeemPromo['discount_value']}%" : 'Rp' . number_format($redeemPromo['discount_value'], 0, ',', '.')) . '!') }}
                </p>
                <div style="background:rgba(255,255,255,0.05);border:1px dashed rgba(255,255,255,0.2);border-radius:8px;padding:10px;margin-bottom:16px;font-family:monospace;font-size:1rem;letter-spacing:0.1em;color:#40E0D0;">{{ $redeemPromo['code'] }}</div>
                <a href="{{ route('plus') }}#plans" onclick="document.getElementById('redeemPromoPopup').classList.remove('active')" class="plus-cta-btn" style="display:inline-block;font-size:0.85rem;text-decoration:none;">→ Lihat Paket Plus</a>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const promoCode = params.get('promo');
            if (promoCode) {
                const field = document.getElementById('promoCodeField');
                const input = document.getElementById('promoCodeInput');
                if (field && input) {
                    field.value = promoCode;
                    input.value = promoCode;
                    setTimeout(applyPromo, 300);
                }
            }
        });
    </script>

    @endsection
