<div x-data="{ showPw: false }" style="display: flex; flex-direction: column; gap: 1.25rem;">
    <div>
        <h1 class="tk-auth-heading">{{ $this->getHeading() }}</h1>
        <p class="tk-auth-desc">
            Masukkan email staf dan kata sandi Anda untuk membuka ruang operasional.
        </p>
    </div>

    @if($isLocalDemoAllowed)
        <div class="tk-auth-alert-info" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
            <span>
                <strong>Mode Lokal Aktif:</strong> Isi cepat kredensial demo {{ $panelId === 'admin' ? 'Admin' : 'Teknisi' }} untuk menguji panel.
            </span>
            <button
                type="button"
                wire:click="fillLocalDemoAccount"
                class="tk-btn tk-btn-sm"
            >
                Isi Akun Demo
            </button>
        </div>
    @endif

    <form wire:submit="authenticate" class="tk-auth-form">
        <div>
            <label for="login-email" class="tk-label">Alamat Email Staf</label>
            <div class="tk-auth-input-wrap">
                <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <input
                    id="login-email"
                    type="email"
                    wire:model="email"
                    placeholder="{{ $panelId === 'admin' ? 'admin@tokoku.local' : 'teknisi@tokoku.local' }}"
                    autocomplete="username"
                    autofocus
                    required
                    class="tk-auth-input"
                >
            </div>
            @error('email')
                <p class="tk-auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="tk-auth-label-row">
                <label for="login-password" class="tk-label" style="margin-bottom: 0;">Kata Sandi</label>
                @if($hasPasswordReset && $passwordResetUrl)
                    <a href="{{ $passwordResetUrl }}" class="tk-auth-link">
                        Lupa kata sandi?
                    </a>
                @endif
            </div>
            <div class="tk-auth-input-wrap">
                <svg class="tk-auth-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <input
                    id="login-password"
                    :type="showPw ? 'text' : 'password'"
                    wire:model="password"
                    placeholder="Masukkan kata sandi"
                    autocomplete="current-password"
                    required
                    class="tk-auth-input tk-auth-input-pw"
                >
                <button
                    type="button"
                    @click="showPw = !showPw"
                    class="tk-auth-pw-toggle"
                    :aria-label="showPw ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'"
                >
                    <span x-text="showPw ? 'Tutup' : 'Lihat'">Lihat</span>
                </button>
            </div>
            @error('password')
                <p class="tk-auth-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <span class="sr-only">Verifikasi Keamanan</span>
        @include('filament.components.recaptcha-box', [
            'isGoogleRecaptcha' => $isGoogleRecaptcha,
            'recaptchaSiteKey' => $recaptchaSiteKey,
            'challengePrompt' => $challengePrompt,
        ])

        <label style="display: inline-flex; align-items: center; gap: 0.55rem; min-height: 2.5rem; font-size: 0.84rem; color: var(--tk-ink); cursor: pointer;">
            <input type="checkbox" wire:model="remember" style="width: 1rem; height: 1rem; accent-color: var(--tk-accent);">
            <span>Ingat sesi saya di perangkat ini</span>
        </label>

        <button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="authenticate"
            class="tk-auth-submit"
        >
            <span wire:loading.remove wire:target="authenticate">
                Masuk ke {{ $panelId === 'admin' ? 'Panel Admin' : 'Meja Teknisi' }}
            </span>
            <span wire:loading wire:target="authenticate">Memverifikasi Akses...</span>
        </button>
    </form>
</div>
