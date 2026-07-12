<div class="detail-extra detail-tab-info">
    {{-- Tagline + Sinopsis --}}
    @if ($movie['tagline'])
        <p class="detail-tagline">"{{ $movie['tagline'] }}"</p>
    @endif
    <h3 class="detail-section-label">Sinopsis</h3>
    <p class="detail-synopsis">{{ $movie['overview'] }}</p>

    {{-- Pembuat --}}
    <section class="detail-crew-section" aria-labelledby="detail-crew-title">
        <h3 id="detail-crew-title" class="detail-section-label">Pembuat</h3>
        <div class="detail-crew-grid">
            <div class="detail-crew">
                <p class="crew-name">{{ $movie['director'] ?? 'Belum tersedia' }}</p>
                <p class="crew-role">Sutradara</p>
            </div>
            @if ($movie['writers'])
                <div class="detail-crew">
                    <p class="crew-name">{{ $movie['writers'] }}</p>
                    <p class="crew-role">Penulis</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Info Film (facts) --}}
    @if (! empty($movie['facts']))
        <section class="detail-facts-section" aria-labelledby="detail-facts-title">
            <h3 id="detail-facts-title" class="detail-section-label">Info Film</h3>
            <div class="detail-facts-grid">
                @foreach ($movie['facts'] as $fact)
                    <article class="detail-fact">
                        <span class="fact-label">{{ $fact['label'] }}</span>
                        <strong class="fact-value">{{ $fact['value'] }}</strong>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Pemeran (cast) --}}
    @if (! empty($movie['cast']))
        <section class="detail-cast-section" aria-labelledby="detail-cast-title">
            <h3 id="detail-cast-title" class="detail-section-label">Pemeran</h3>
            <div class="detail-cast-row">
                @foreach ($movie['cast'] as $castMember)
                    <article class="cast-card">
                        @if ($castMember['profile_url'])
                            <img class="cast-photo" src="{{ $castMember['profile_url'] }}" alt="Foto {{ $castMember['name'] }}" loading="lazy">
                        @else
                            <div class="cast-photo cast-photo-placeholder">No Photo</div>
                        @endif
                        <div class="cast-info">
                            <p class="cast-name">{{ $castMember['name'] }}</p>
                            <p class="cast-character">{{ $castMember['character'] ?: 'Peran belum tersedia' }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Forms for auth users, login prompt for guests --}}
    @auth
        <div class="user-forms">
            <details class="detail-form-section">
                <summary class="detail-form-toggle">
                    <span>📖</span> Tulis Diary
                </summary>
                <form method="POST" action="{{ route('movies.diary.store', $movieId) }}" class="detail-form">
                    @csrf
                    <div class="form-row-2col">
                        <div class="form-row">
                            <label class="form-label" for="watched_at">Tanggal Nonton</label>
                            <input id="watched_at" type="date" name="watched_at" class="form-input"
                                value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-row">
                            <label class="form-label" for="mood">Mood</label>
                            <select id="mood" name="mood" class="form-select">
                                <option value="">Pilih mood...</option>
                                <option value="happy">😊 Happy</option>
                                <option value="thrilled">🤩 Thrilled</option>
                                <option value="moved">🥹 Moved</option>
                                <option value="sad">😢 Sad</option>
                                <option value="scared">😨 Scared</option>
                                <option value="inspired">✨ Inspired</option>
                                <option value="nostalgic">🌅 Nostalgic</option>
                                <option value="bored">😑 Bored</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="form-label" for="notes">Catatan</label>
                        <textarea id="notes" name="notes" class="form-textarea" placeholder="Cerita singkat soal pengalamanmu menonton film ini..." rows="3"></textarea>
                    </div>
                    <div class="form-footer">
                        <label class="form-check-label">
                            <input type="checkbox" name="is_rewatch" value="1" class="form-checkbox">
                            Ini rewatch
                        </label>
                        <button type="submit" class="form-submit">Simpan</button>
                    </div>
                </form>
            </details>

            <details id="review-form" class="detail-form-section">
                <summary class="detail-form-toggle">
                    <span>✏️</span> Tulis Review
                </summary>
                <form method="POST" action="{{ route('movies.review.store', $movieId) }}" class="detail-form">
                    @csrf
                    <div class="form-row">
                        <label class="form-label">Rating</label>
                        <div style="display: flex; gap: 4px; margin-top: 8px;" id="rating-picker">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" onclick="selectRating({{ $i }})" data-rating="{{ $i }}"
                                    class="rating-btn" aria-label="{{ $i }} bintang">
                                    ★
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="rating-input" required>
                        <p id="rating-hint">Tap bintang untuk memilih rating</p>
                    </div>
                    <script>
                    function selectRating(n) {
                        document.getElementById('rating-input').value = n;
                        document.querySelectorAll('#rating-picker button').forEach(btn => {
                            const val = parseInt(btn.getAttribute('data-rating'));
                            if (val <= n) {
                                btn.classList.add('rating-btn--active');
                            } else {
                                btn.classList.remove('rating-btn--active');
                            }
                        });
                        document.getElementById('rating-hint').style.display = 'none';
                    }
                    </script>
                    <div class="form-row">
                        <label class="form-label" for="body">Reviewmu</label>
                        <textarea id="body" name="body" class="form-textarea" placeholder="Tulis pendapatmu tentang film ini..." rows="4"></textarea>
                    </div>
                    <div class="form-footer">
                        <label class="form-check-label">
                            <input type="checkbox" name="has_spoiler" value="1" class="form-checkbox">
                            Mengandung spoiler
                        </label>
                        <button type="submit" class="form-submit">Simpan</button>
                    </div>
                </form>
            </details>
        </div>
    @else
        <a href="{{ route('login') }}" class="user-action-login-prompt">
            Masuk untuk menyimpan & mencatat film ini →
        </a>
    @endauth
</div>
