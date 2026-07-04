<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-lg border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold text-zinc-100 shadow-sm transition ease-in-out duration-150 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-lime-300 focus:ring-offset-2 focus:ring-offset-zinc-950 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
