<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg border border-lime-200 bg-lime-300 px-5 py-3 text-sm font-black text-black transition duration-150 ease-in-out hover:bg-white focus:outline-none focus:ring-2 focus:ring-lime-300 focus:ring-offset-2 focus:ring-offset-zinc-950 active:bg-lime-200']) }}>
    {{ $slot }}
</button>
