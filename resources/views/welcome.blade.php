<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jakka Space - Movie Indonesia</title>
    <meta name="description" content="Jakka Space menampilkan daftar film populer dari TMDB.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/peace-sans" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background: #000;
            color: #fff;
            font-family: 'Inter', sans-serif;
        }

        #navbar {
            position: fixed;
            top: 0;
            z-index: 100;
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            padding: 20px 4vw;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.85), transparent);
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.8);
        }

        .nav-logo {
            position: relative;
            display: flex;
            align-items: flex-end;
            gap: 0.4em;
            padding: 6px 0;
            text-decoration: none;
        }

        .nav-logo::before,
        .nav-logo::after {
            position: absolute;
            left: 0;
            width: 100%;
            height: 3px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            content: '';
        }

        .nav-logo::before {
            top: -2px;
        }

        .nav-logo::after {
            bottom: 0;
        }

        .nav-jakka,
        .nav-letter {
            font-family: 'Peace Sans', sans-serif;
            font-size: 1.3rem;
            font-weight: normal;
            letter-spacing: 0;
            line-height: 1;
            text-transform: uppercase;
        }

        .nav-jakka {
            color: #fff;
        }

        .nav-space-wrap {
            display: flex;
            align-items: flex-end;
        }

        .nav-letter {
            display: inline-flex;
            align-items: center;
        }

        .nav-center {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0;
            opacity: 0.5;
            text-decoration: none;
            text-transform: uppercase;
            transition: opacity 0.25s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            opacity: 1;
        }

        .nav-search {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 10px;
            width: 14px;
            height: 14px;
            color: rgba(255, 255, 255, 0.5);
            pointer-events: none;
        }

        .search-input {
            width: 200px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 4px;
            outline: none;
            background: transparent;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            padding: 8px 16px 8px 32px;
            transition: border-color 0.3s ease, background 0.3s ease;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .search-input:focus {
            border-color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .hamburger {
            z-index: 101;
            display: none;
            border: none;
            background: none;
            cursor: pointer;
            padding: 4px;
        }

        .hamburger-line {
            display: block;
            width: 18px;
            height: 1.5px;
            margin: 4px 0;
            background: #fff;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .movies-page-content {
            min-height: 100vh;
            padding-top: 92px;
            background: #000;
            animation: pageEnter 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) both;
        }

        @keyframes pageEnter {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .movie-section {
            padding: 40px 4vw 44px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: #000;
        }

        .row-title {
            margin-bottom: 20px;
            color: #fff;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.8rem;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .section-kicker {
            max-width: 720px;
            margin: -8px 0 28px;
            color: rgba(255, 255, 255, 0.56);
            font-size: 0.9rem;
            line-height: 1.7;
        }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 28px 16px;
        }

        .movie-card-sm {
            position: relative;
            min-width: 0;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .movie-card-sm:hover {
            transform: translateY(-6px);
        }

        .card-poster-wrap {
            position: relative;
            width: 100%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            aspect-ratio: 2 / 3;
            background: #111;
            transition: border-color 0.3s ease;
        }

        .card-poster-wrap img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: filter 0.3s ease, transform 0.3s ease;
        }

        .movie-card-sm:hover .card-poster-wrap {
            border-color: rgba(255, 255, 255, 0.3);
        }

        .movie-card-sm:hover .card-poster-wrap img {
            filter: brightness(0.6);
            transform: scale(1.03);
        }

        .card-rank {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 2;
            display: flex;
            width: 32px;
            height: 32px;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.6rem;
            line-height: 1;
        }

        .badge-sewa,
        .badge-beli {
            position: absolute;
            bottom: 6px;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 4px 8px;
            backdrop-filter: blur(4px);
        }

        .badge-sewa {
            left: 6px;
            border: 1px solid rgba(0, 102, 255, 0.5);
            background: rgba(0, 102, 255, 0.3);
        }

        .badge-beli {
            right: 6px;
            border: 1px solid rgba(0, 153, 51, 0.5);
            background: rgba(0, 153, 51, 0.3);
        }

        .card-info-sm {
            padding: 8px 2px;
        }

        .card-rating {
            display: block;
            margin-top: 4px;
            color: #7a7a7a;
            font-size: 11px;
        }

        .card-title-sm {
            display: -webkit-box;
            overflow: hidden;
            margin-top: 3px;
            color: #fff;
            font-size: 12px;
            font-weight: 400;
            line-height: 1.3;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .empty-state {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            color: rgba(255, 255, 255, 0.62);
            padding: 28px;
            text-align: center;
        }

        .no-poster {
            display: flex;
            height: 100%;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.45);
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.4rem;
            letter-spacing: 0;
            text-align: center;
        }

        #footer {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            background: #000;
            color: #7a7a7a;
            font-size: 10px;
            letter-spacing: 0;
            padding: 50px 4vw;
            text-transform: uppercase;
        }

        @media (max-width: 1024px) {
            .search-input {
                width: 160px;
            }
        }

        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .nav-center {
                position: fixed;
                top: 0;
                right: -100%;
                z-index: 100;
                width: 260px;
                height: 100vh;
                flex-direction: column;
                align-items: flex-start;
                gap: 30px;
                border-left: 1px solid rgba(255, 255, 255, 0.08);
                background: rgba(0, 0, 0, 0.55);
                padding: 80px 30px 40px;
                backdrop-filter: blur(24px);
                transition: right 0.35s ease;
            }

            .nav-center.open {
                right: 0;
            }

            .nav-links {
                flex-direction: column;
                gap: 20px;
            }

            .nav-links a {
                font-size: 14px;
            }

            .nav-search {
                order: -1;
                width: 100%;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                padding-bottom: 10px;
            }

            .search-input {
                width: 100%;
            }

            #navbar {
                padding: 12px 5vw;
            }

            .nav-jakka,
            .nav-letter {
                font-size: 0.85rem;
            }

            .nav-logo {
                gap: 0.25em;
                padding: 4px 0;
            }

            .nav-logo::before,
            .nav-logo::after {
                height: 2px;
            }

            .movies-page-content {
                padding-top: 68px;
            }

            .movie-section {
                padding: 24px 5vw 34px;
            }

            .row-title {
                margin-bottom: 16px;
                font-size: 1.45rem;
            }

            .movie-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 18px 8px;
            }

            .movie-card-sm:hover {
                transform: none;
            }

            .card-rank {
                top: 4px;
                left: 4px;
                width: 18px;
                height: 18px;
                font-size: 0.85rem;
            }

            .badge-sewa,
            .badge-beli {
                font-size: 0.42rem;
                padding: 2px 3px;
            }

            #footer {
                flex-direction: column;
                align-items: center;
                gap: 10px;
                padding: 30px 6vw;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .movies-page-content {
                padding-top: 60px;
            }

            .movie-section {
                padding: 20px 4vw 30px;
            }

            .movie-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 16px 7px;
            }

            .card-info-sm {
                padding-top: 7px;
            }

            .card-title-sm {
                font-size: 8px;
                line-height: 1.25;
            }

            .card-rating {
                font-size: 7px;
            }

            .badge-sewa {
                left: 3px;
                bottom: 18px;
            }

            .badge-beli {
                right: auto;
                bottom: 3px;
                left: 3px;
            }
        }

        @media (max-width: 360px) {
            .movie-grid {
                gap: 14px 5px;
            }

            .badge-sewa,
            .badge-beli {
                font-size: 0.36rem;
                padding: 1px 2px;
            }

            .card-rank {
                width: 16px;
                height: 16px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="movie-page anim-started">
    <nav id="navbar">
        <a href="{{ url('/') }}" class="nav-logo" aria-label="Jakka Space">
            <span class="nav-jakka">JAKKA</span>
            <span class="nav-space-wrap">
                <span class="nav-letter" style="color:#40E0D0;">S</span>
                <span class="nav-letter" style="color:#FF0000;">P</span>
                <span class="nav-letter" style="color:#FF69B4;">A</span>
                <span class="nav-letter" style="color:#00FF00;">C</span>
                <span class="nav-letter" style="color:#8A2BE2;">E</span>
            </span>
        </a>

        <div class="nav-center" id="nav-center">
            <ul class="nav-links">
                <li><a href="{{ url('/') }}" class="active">HOME</a></li>
                <li><a href="#all-movies">ALL MOVIE</a></li>
            </ul>

            <div class="nav-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Cari Film..." class="search-input" id="search-input" aria-label="Cari Film">
            </div>
        </div>

        <button class="hamburger" id="hamburger" type="button" aria-label="Menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </nav>

    <main class="movies-page-content">
        <section class="movie-section" id="all-movies">
            <h1 class="row-title">Jakkaspace Movie Indonesia</h1>
            <p class="section-kicker">Film populer dari TMDB.</p>

            <div class="movie-grid" id="all-movies-grid">
                @if (empty($movies))
                    <div class="empty-state">Belum ada film yang bisa ditampilkan.</div>
                @else
                    @foreach($movies as $movie)
                    <article class="movie-card-sm" data-title="{{ strtolower($movie['title'] ?? '') }}">
                        <div class="card-rank">{{ $loop->iteration }}</div>

                        <div class="card-poster-wrap">
                            @if (! empty($movie['poster_path']))
                                <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="Poster {{ $movie['title'] ?? 'film' }}" loading="lazy">
                            @else
                                <div class="no-poster">No Poster</div>
                            @endif

                            <div class="badge-sewa">SEWA 5K</div>
                            <div class="badge-beli">BELI 15K</div>
                        </div>

                        <div class="card-info-sm">
                            <span class="card-rating">Rating {{ number_format((float) ($movie['vote_average'] ?? 0), 1) }}</span>
                            <p class="card-title-sm">{{ $movie['title'] ?? 'Tanpa Judul' }}</p>
                        </div>
                    </article>
                    @endforeach
                @endif
            </div>
        </section>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>

    <script>
        const hamburger = document.getElementById('hamburger');
        const navCenter = document.getElementById('nav-center');
        const searchInput = document.getElementById('search-input');
        const movieCards = document.querySelectorAll('.movie-card-sm');
        const clock = document.getElementById('clock');

        hamburger?.addEventListener('click', () => {
            navCenter?.classList.toggle('open');
        });

        searchInput?.addEventListener('input', (event) => {
            const query = event.target.value.toLowerCase().trim();

            movieCards.forEach((card) => {
                const title = card.dataset.title || '';
                card.style.display = title.includes(query) ? '' : 'none';
            });
        });

        function updateClock() {
            const time = new Intl.DateTimeFormat('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
                timeZone: 'Asia/Jakarta',
            }).format(new Date());

            if (clock) {
                clock.textContent = `YOGYAKARTA - ${time}`;
            }
        }

        updateClock();
        setInterval(updateClock, 1000 * 30);
    </script>
</body>
</html>
