@props([
    'recaptchaConfigured' => false,
    'recaptchaSiteKey' => '',
    'isUsingTestKey' => false,
])

<div class="tk-auth-captcha-box">
    <span style="font-size: 0.78rem; font-weight: 700; color: var(--tk-ink); display: inline-flex; align-items: center; gap: 0.4rem;">
        <svg style="width: 1rem; height: 1rem; color: var(--tk-accent);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <span>Verifikasi Google reCAPTCHA</span>
    </span>

    @if($recaptchaConfigured)
        <div
            wire:ignore
            x-data="{
                widgetId: null,
                mountRecaptcha() {
                    const renderBox = () => {
                        if (!window.grecaptcha || !window.grecaptcha.render || this.widgetId !== null || !this.$refs.box) return;
                        this.$refs.box.innerHTML = '';
                        this.widgetId = window.grecaptcha.render(this.$refs.box, {
                            sitekey: @js($recaptchaSiteKey),
                            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                            callback: (token) => { $wire.set('recaptchaToken', token); },
                            'expired-callback': () => { $wire.set('recaptchaToken', ''); },
                            'error-callback': () => { $wire.set('recaptchaToken', ''); }
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
                        window.grecaptcha.ready(renderBox);
                    } else {
                        window.addEventListener('tokoku-grecaptcha-loaded', () => {
                            if (window.grecaptcha && window.grecaptcha.ready) {
                                window.grecaptcha.ready(renderBox);
                            } else {
                                renderBox();
                            }
                        }, { once: true });
                    }

                    window.addEventListener('tokoku-recaptcha-reset', () => {
                        if (window.grecaptcha && this.widgetId !== null) {
                            window.grecaptcha.reset(this.widgetId);
                        }
                    });
                }
            }"
            x-init="mountRecaptcha()"
            class="tk-recaptcha-mount"
        >
            <div x-ref="box"></div>
        </div>

        @if($isUsingTestKey && app()->isLocal())

        @endif
    @else
        <div class="tk-auth-alert-info">
            Kunci <code class="tk-mono">RECAPTCHA_SITE_KEY</code> dan <code class="tk-mono">RECAPTCHA_SECRET_KEY</code> belum diatur pada berkas <code class="tk-mono">.env</code>.
        </div>
    @endif

    @error('recaptchaToken')
        <p class="tk-auth-error" role="alert">{{ $message }}</p>
    @enderror
</div>
