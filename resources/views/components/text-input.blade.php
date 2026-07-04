@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-lg border border-white/10 bg-white/[0.06] px-4 py-3 text-white shadow-sm placeholder:text-zinc-500 focus:border-lime-300 focus:ring-lime-300 disabled:opacity-60']) }}>
