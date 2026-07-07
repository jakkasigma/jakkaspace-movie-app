@props(['user', 'class' => '', 'href' => null])

@if ($href)
    <a href="{{ $href }}" class="{{ $class }} {{ $user->isPlus() ? 'username-plus' : '' }}"
       @if ($user->isPlus() && $user->theme) style="--plus-accent: {{ $user->theme->accent_color }}" @endif>
        {{ $user->name }}
        @if ($user->isPlus() && $user->theme?->badge_icon)
            <span class="plus-badge">{{ $user->theme->badge_icon }}</span>
        @endif
    </a>
@else
    <span class="{{ $class }} {{ $user->isPlus() ? 'username-plus' : '' }}"
          @if ($user->isPlus() && $user->theme) style="--plus-accent: {{ $user->theme->accent_color }}" @endif>
        {{ $user->name }}
        @if ($user->isPlus() && $user->theme?->badge_icon)
            <span class="plus-badge">{{ $user->theme->badge_icon }}</span>
        @endif
    </span>
@endif
