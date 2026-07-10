<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Jakka Space') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;600;700&family=Lora:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body">
    <main class="auth-layout">
        {{-- Back to home --}}
        <a href="{{ route('movies.index') }}" class="auth-layout-logo" aria-label="Jakka Space">
            <span class="nav-jakka">JAKKA</span>
            <span class="nav-space-wrap">
                <span class="nav-letter" style="color:#40E0D0;">S</span>
                <span class="nav-letter" style="color:#FF0000;">P</span>
                <span class="nav-letter" style="color:#FF69B4;">A</span>
                <span class="nav-letter" style="color:#00FF00;">C</span>
                <span class="nav-letter" style="color:#8A2BE2;">E</span>
            </span>
        </a>

        <section class="auth-card">
            {{-- Left panel --}}
            <div class="auth-panel-left">
                <div class="auth-panel-left-badge">
                    <span class="auth-panel-left-badge-bar"></span>
                    <span class="auth-panel-left-badge-text">Your Space</span>
                </div>
                <div>
                    <p class="auth-panel-left-title">Masuk ke ruang film kamu.</p>
                    <p class="auth-panel-left-desc">Lanjutkan eksplorasi film dengan tampilan gelap yang tetap bersih dan fokus.</p>
                </div>
                <div class="auth-panel-left-footer">
                    <span class="auth-panel-left-footer-item">TMDB</span>
                    <span class="auth-panel-left-footer-item">Diary</span>
                    <span class="auth-panel-left-footer-item">Community</span>
                </div>
            </div>

            {{-- Right panel --}}
            <div class="auth-panel-right">
                {{ $slot }}
            </div>
        </section>
    </main>
</body>
</html>
