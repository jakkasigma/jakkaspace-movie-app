<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Jakka Space')</title>
    <meta name="description" content="@yield('description', 'Jakka Space — personal movie diary dan platform film.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://image.tmdb.org">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;600;700&family=Lora:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/peace-sans" rel="stylesheet">
    @php
        $hotFilePath = public_path('hot');
        $hotUrl = file_exists($hotFilePath) ? trim(file_get_contents($hotFilePath)) : null;
        $hotParts = is_string($hotUrl) ? parse_url($hotUrl) : false;
        $shouldUseHotAssets = false;

        if (is_array($hotParts) && isset($hotParts['host'], $hotParts['port'])) {
            $socket = @fsockopen($hotParts['host'], (int) $hotParts['port'], $errorNumber, $errorMessage, 0.3);

            if (is_resource($socket)) {
                fclose($socket);
                $shouldUseHotAssets = true;
            }
        }

        $buildManifestPath = public_path('build/manifest.json');
        $buildManifest = file_exists($buildManifestPath)
            ? json_decode(file_get_contents($buildManifestPath), true)
            : null;
        $buildEntries = is_array($buildManifest) ? array_values($buildManifest) : [];
        $compiledCss = collect($buildEntries)->first(
            fn (array $entry): bool => str_ends_with($entry['file'] ?? '', '.css'),
        );
        $compiledJs = collect($buildEntries)->first(
            fn (array $entry): bool => str_ends_with($entry['file'] ?? '', '.js'),
        );
        $forwardedHost = request()->headers->get('x-forwarded-host');
        $isTunneledRequest = is_string($forwardedHost) && $forwardedHost !== '';
        $shouldUseHotAssets = $shouldUseHotAssets && ! $isTunneledRequest;
    @endphp

    @if ($shouldUseHotAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        @if ($compiledCss)
            <link rel="stylesheet" href="/build/{{ $compiledCss['file'] }}">
        @endif
        @if ($compiledJs)
            <script type="module" src="/build/{{ $compiledJs['file'] }}"></script>
        @endif
    @endif

    @stack('head')
</head>
<body class="@yield('body-class')">
    @yield('body')
    @unless(request()->is('login', 'register', 'forgot-password', 'reset-password', 'verify-email', 'confirm-password', 'inbox/*'))
        <x-bottom-nav />
    @endunless
</body>
</html>
