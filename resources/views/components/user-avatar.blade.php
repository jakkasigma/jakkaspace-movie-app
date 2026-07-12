@props([
    'user',
    'class' => '',
    'placeholderClass' => '',
])

@php
    $initial = strtoupper(substr($user->name, 0, 1));
    $onerror = "this.onerror=null;this.style.display='none';this.parentNode.querySelector('[data-avatar-fallback]')?.classList.remove('avatar-fallback-hidden');";
@endphp

@if ($user->isPlus() && $user->theme)
    <div class="{{ $class }} avatar-premium-wrap" style="--avatar-border: {{ $user->theme->avatar_border_css }}">
        @if ($user->avatar_url)
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="avatar-premium-img" onerror="{{ $onerror }}">
            <div class="avatar-premium-placeholder avatar-fallback-hidden" data-avatar-fallback>{{ $initial }}</div>
        @else
            <div class="{{ $placeholderClass }} avatar-premium-placeholder">{{ $initial }}</div>
        @endif
    </div>
@else
    @if ($user->avatar_url)
        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="{{ $class }}" onerror="{{ $onerror }}">
        <div class="{{ $placeholderClass ?: $class }} avatar-fallback-hidden" data-avatar-fallback>{{ $initial }}</div>
    @else
        <div class="{{ $placeholderClass ?: $class }}">{{ $initial }}</div>
    @endif
@endif
