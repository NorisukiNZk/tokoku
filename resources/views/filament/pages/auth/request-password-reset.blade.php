<div style="display: flex; flex-direction: column; gap: 1.25rem;">
    <div>
        <h1 class="tk-auth-heading">{{ $this->getHeading() }}</h1>
        <p class="tk-auth-desc">
            Masukkan alamat email staf yang terdaftar. Kami akan mengirimkan tautan aman untuk mengatur ulang kata sandi Anda.
        </p>
    </div>

    @if($resetRequested)
        <div class="tk-auth-alert-ok" role="status">
            <strong>Permintaan terkirim.</strong> Jika email terdaftar sebagai staf pada panel ini, tautan pemulihan kata sandi sudah dikirimkan ke kotak masuk Anda.
            @if($isLogMailer)
                <div style="margin-top: 0.45rem; font-size: 0.78rem;">
                    Catatan mode pengembangan lokal (<code class="tk-mono">MAIL_MAILER=log</code>): buka berkas <code class="tk-mono">storage/logs/laravel.log</code> untuk menyalin tautan reset secara langsung.
                </div>
            @endif
        </div>
    @endif

    <form wire:submit="request" class="tk-auth-form">
        <div>
            <label for="reset-email" class="tk-label">Alamat Email Staf</label>
            <div class="tk-auth-input-wrap">
                <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <input
                    id="reset-email"
                    type="email"
                    wire:model="email"
                    placeholder="nama@tokoku.local"
                    autocomplete="email"
                    autofocus
                    required
                    class="tk-auth-input"
                >
            </div>
            @error('email')
                <p class="tk-auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        @include('filament.components.recaptcha-box', [
            'isGoogleRecaptcha' => $isGoogleRecaptcha,
            'recaptchaSiteKey' => $recaptchaSiteKey,
            'challengePrompt' => $challengePrompt,
        ])

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="request"
            class="tk-auth-submit"
        >
            <span wire:loading.remove wire:target="request">Kirim Tautan Pemulihan Sandi</span>
            <span wire:loading wire:target="request">Memproses Permintaan...</span>
        </button>

        <div style="text-align: center; padding-top: 0.25rem;">
            <a href="{{ $loginUrl }}" class="tk-auth-link">
                &larr; Kembali ke halaman login
            </a>
        </div>
    </form>
</div>
