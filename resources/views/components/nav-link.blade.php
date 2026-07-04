@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-lime-300 px-1 pt-1 text-sm font-medium leading-5 text-white transition duration-150 ease-in-out focus:outline-none focus:border-lime-200'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-zinc-400 transition duration-150 ease-in-out hover:border-white/30 hover:text-white focus:outline-none focus:border-white/30 focus:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
