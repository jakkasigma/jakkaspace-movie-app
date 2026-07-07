<x-guest-layout>
    <div class="auth-form-wrap">
        <div class="auth-form-heading">
            <p class="auth-form-kicker">Verifikasi Email</p>
            <h2 class="auth-form-title">Cek email kamu.</h2>
            <p class="auth-form-desc">Kami sudah kirim link verifikasi ke <strong>{{ Auth::user()->email }}</strong>. Klik link tersebut untuk mengaktifkan akunmu.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="auth-status-msg auth-status-success">Link verifikasi baru sudah dikirim ke email kamu.</div>
        @endif

        <div class="auth-verify-actions">
            <form method="POST" action="{{ route('verification.send') }}" class="auth-form">
                @csrf
                <button type="submit" class="form-submit">Kirim Ulang Email</button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="auth-verify-logout-form">
                @csrf
                <button type="submit" class="auth-link-muted">← Ganti email atau logout</button>
            </form>
        </div>
    </div>
</x-guest-layout>
