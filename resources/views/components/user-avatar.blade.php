@props([
    'user',
    'class' => '',
    'placeholderClass' => '',
])

@php
    $initial = strtoupper(substr($user->name, 0, 1));
@endphp

@if ($user->isPlus() && $user->theme)
    <div class="{{ $class }} avatar-premium-wrap" style="--avatar-border: {{ $user->theme->avatar_border_css }}">
        @if ($user->avatar_url)
            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="avatar-premium-img">
        @else
            <div class="{{ $placeholderClass }} avatar-premium-placeholder">{{ $initial }}</div>
        @endif
    </div>
@else
    @if ($user->avatar_url)
        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="{{ $class }}">
    @else
        <div class="{{ $placeholderClass ?: $class }}">{{ $initial }}</div>
    @endif
@endif
