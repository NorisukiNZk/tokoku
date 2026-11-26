@props([
    'isGoogleRecaptcha' => false,
    'recaptchaSiteKey' => '',
    'challengePrompt' => '',
])

<div class="tk-auth-captcha-box">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
        <span style="font-size: 0.78rem; font-weight: 700; color: var(--tk-ink); display: inline-flex; align-items: center; gap: 0.4rem;">
            <svg style="width: 1rem; height: 1rem; color: var(--tk-accent);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Verifikasi Keamanan (reCAPTCHA)</span>
        </span>

        @if(!$isGoogleRecaptcha)
            <button
                type="button"
                wire:click="refreshSecurityChallenge"
                class="tk-btn tk-btn-sm"
            >
                Soal Baru
            </button>
        @endif
    </div>

    @if($isGoogleRecaptcha)
        <div
            wire:ignore
            x-data="{
                widgetId: null,
                mountRecaptcha() {
                    const renderBox = () => {
                        if (!window.grecaptcha || !window.grecaptcha.render || this.widgetId !== null) return;
                        this.widgetId = window.grecaptcha.render(this.$refs.box, {
                            sitekey: @js($recaptchaSiteKey),
                            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                            callback: (token) => { $wire.set('recaptchaToken', token); },
                            'expired-callback': () => { $wire.set('recaptchaToken', ''); }
                        });
                    };

                    if (!document.getElementById('tokoku-grecaptcha-script')) {
                        window.__tokokuRecaptchaReady = () => {
                            window.dispatchEvent(new CustomEvent('tokoku-grecaptcha-loaded'));
                        };
                        const s = document.createElement('script');
                        s.id = 'tokoku-grecaptcha-script';
                        s.src = 'https://www.google.com/recaptcha/api.js?onload=__tokokuRecaptchaReady&render=explicit&hl=id';
                        s.async = true;
                        s.defer = true;
                        document.head.appendChild(s);
                    }

                    if (window.grecaptcha && window.grecaptcha.render) {
                        renderBox();
                    } else {
                        window.addEventListener('tokoku-grecaptcha-loaded', renderBox, { once: true });
                    }

                    window.addEventListener('tokoku-recaptcha-reset', () => {
                        if (window.grecaptcha && this.widgetId !== null) {
                            window.grecaptcha.reset(this.widgetId);
                        }
                    });
                }
            }"
            x-init="mountRecaptcha()"
        >
            <div x-ref="box"></div>
        </div>
        @error('recaptchaToken')
            <p class="tk-auth-error" role="alert">{{ $message }}</p>
        @enderror
    @else
        <div class="tk-auth-captcha-row">
            <label for="input-verifikasi-keamanan" style="font-size: 0.86rem; font-weight: 600; color: var(--tk-ink);">
                {{ $challengePrompt }}
            </label>
            <input
                id="input-verifikasi-keamanan"
                type="text"
                inputmode="numeric"
                wire:model="securityAnswer"
                placeholder="Jawaban"
                autocomplete="off"
                required
                class="tk-auth-captcha-input"
            >
        </div>
        <p style="font-size: 0.72rem; color: var(--tk-muted); margin: 0;">
            Verifikasi lokal aktif. Isi <code class="tk-mono">RECAPTCHA_SITE_KEY</code> &amp; <code class="tk-mono">RECAPTCHA_SECRET_KEY</code> pada <code class="tk-mono">.env</code> untuk beralih otomatis ke widget Google reCAPTCHA v2.
        </p>
        @error('securityAnswer')
            <p class="tk-auth-error" role="alert">{{ $message }}</p>
        @enderror
    @endif
</div>
