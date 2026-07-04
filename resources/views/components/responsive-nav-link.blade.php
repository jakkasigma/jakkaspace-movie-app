@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-lime-300 bg-white/10 py-2 pe-4 ps-3 text-start text-base font-medium text-white transition duration-150 ease-in-out focus:outline-none focus:border-lime-200'
            : 'block w-full border-l-4 border-transparent py-2 pe-4 ps-3 text-start text-base font-medium text-zinc-400 transition duration-150 ease-in-out hover:border-white/30 hover:bg-white/5 hover:text-white focus:outline-none focus:border-white/30 focus:bg-white/5 focus:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
