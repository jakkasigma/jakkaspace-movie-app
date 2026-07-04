@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-zinc-200']) }}>
    {{ $value ?? $slot }}
</label>
