<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-lime-300">Akun</p>
                <h2 class="text-2xl font-black leading-tight text-white">
                    Your Space
                </h2>
            </div>

            <a href="{{ route('movies.index') }}" class="inline-flex items-center justify-center rounded-lg border border-white/15 px-4 py-2 text-sm font-semibold text-zinc-100 transition hover:border-lime-300/60 hover:text-white focus:outline-none focus:ring-2 focus:ring-lime-300 focus:ring-offset-2 focus:ring-offset-black">
                Jelajahi Film
            </a>
        </div>
    </x-slot>

    <section class="px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[1.15fr_0.85fr]">
            <div class="rounded-lg border border-white/10 bg-zinc-950 p-6 shadow-2xl shadow-black/30 sm:p-8">
                <div class="flex flex-col gap-5">
                    <p class="text-sm font-semibold text-lime-300">Halo, {{ Auth::user()->name }}</p>
                    <h1 class="max-w-3xl text-3xl font-black leading-tight text-white sm:text-5xl">
                        Selamat datang di ruang film pribadi kamu.
                    </h1>
                    <p class="max-w-2xl text-base leading-7 text-zinc-400">
                        Halaman ini sudah menjadi tujuan setelah login dan register. Nanti bisa dipakai untuk watchlist, histori, atau koleksi film favorit.
                    </p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
                <div class="rounded-lg border border-white/10 bg-white/[0.04] p-5">
                    <p class="text-sm text-zinc-500">Status</p>
                    <p class="text-lg font-black text-white">Login aktif</p>
                </div>

                <div class="rounded-lg border border-white/10 bg-white/[0.04] p-5">
                    <p class="text-sm text-zinc-500">Email</p>
                    <p class="break-words text-lg font-black text-white">{{ Auth::user()->email }}</p>
                </div>

                <div class="rounded-lg border border-white/10 bg-white/[0.04] p-5">
                    <p class="text-sm text-zinc-500">Mode</p>
                    <p class="text-lg font-black text-white">Cinematic dark</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
