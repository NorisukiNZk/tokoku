<?php

namespace App\Filament\Pages\Auth;

use App\Models\StoreSetting;
use App\Models\User;
use App\Services\RecaptchaVerifier;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\PasswordResetResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

class TokokuRequestPasswordReset extends BaseRequestPasswordReset
{
    protected static string $layout = 'filament.layouts.auth-split';
    protected static string $view = 'filament.pages.auth.request-password-reset';

    public string $email = '';
    public string $recoveryPin = '';
    public string $recaptchaToken = '';
    public string $securityAnswer = '';
    public string $challengePrompt = '';

    #[Locked]
    public ?int $verifiedUserId = null;

    #[Locked]
    public string $verifiedEmail = '';

    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());

            return;
        }

        $this->refreshSecurityChallenge();
    }

    public function getTitle(): string | Htmlable
    {
        return 'Lupa Kata Sandi Staf - Tokoku';
    }

    public function getHeading(): string | Htmlable
    {
        return $this->verifiedUserId
            ? 'Buat Kata Sandi Baru'
            : 'Atur Ulang Kata Sandi Instan';
    }

    public function refreshSecurityChallenge(): void
    {
        $verifier = app(RecaptchaVerifier::class);
        $this->securityAnswer = '';
        $this->recaptchaToken = '';

        if (! $verifier->isGoogleConfigured()) {
            $this->challengePrompt = $verifier->generateFallbackChallenge($this->captchaContextKey());
        } else {
            $this->challengePrompt = '';
        }
    }

    public function fillLocalDemoRecovery(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $panelId = Filament::getCurrentPanel()?->getId();
        if ($panelId === 'admin') {
            $this->email = 'admin@tokoku.local';
            $this->recoveryPin = '112233';
        } else {
            $this->email = 'teknisi@tokoku.local';
            $this->recoveryPin = '445566';
        }

        $verifier = app(RecaptchaVerifier::class);
        if (! $verifier->isGoogleConfigured()) {
            $expected = $verifier->currentFallbackAnswerForLocalDemo($this->captchaContextKey());
            if ($expected === null) {
                $this->refreshSecurityChallenge();
                $expected = $verifier->currentFallbackAnswerForLocalDemo($this->captchaContextKey());
            }
            $this->securityAnswer = (string) ($expected ?? '');
        }
    }

    public function verifyPinAndProceed(): void
    {
        $cleanEmail = strtolower(trim($this->email));
        $rateKey = 'tokoku:pin-reset:' . request()->ip() . ':' . $cleanEmail;

        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            throw ValidationException::withMessages([
                'recoveryPin' => "Terlalu banyak percobaan PIN pemulihan. Tunggu {$seconds} detik sebelum mencoba kembali.",
            ]);
        }

        $this->validate([
            'email' => ['required', 'email'],
            'recoveryPin' => ['required', 'string', 'min:6', 'max:40'],
        ], [
            'email.required' => 'Alamat email staf wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'recoveryPin.required' => 'Masukkan 6 digit PIN Pemulihan atau Kode Pemulihan Toko.',
            'recoveryPin.min' => 'PIN Pemulihan minimal 6 karakter.',
        ]);

        $verifier = app(RecaptchaVerifier::class);
        if ($verifier->isGoogleConfigured()) {
            if (! $verifier->verifyGoogleToken($this->recaptchaToken, request()->ip())) {
                $this->recaptchaToken = '';
                $this->dispatch('tokoku-recaptcha-reset');

                throw ValidationException::withMessages([
                    'recaptchaToken' => 'Selesaikan verifikasi Google reCAPTCHA terlebih dahulu.',
                ]);
            }
        } else {
            if (! $verifier->verifyFallbackAnswer($this->captchaContextKey(), $this->securityAnswer)) {
                $this->refreshSecurityChallenge();

                throw ValidationException::withMessages([
                    'securityAnswer' => 'Jawaban verifikasi keamanan belum sesuai.',
                ]);
            }
        }

        $panel = Filament::getCurrentPanel();
        $user = User::query()->where('email', $cleanEmail)->first();

        $submittedSecret = trim($this->recoveryPin);
        $masterCode = trim((string) config('services.tokoku.recovery_code', ''));

        $pinValid = false;
        if ($user && (! $panel || $user->canAccessPanel($panel))) {
            $rawHashedPin = $user->getRawOriginal('recovery_pin');
            if (filled($rawHashedPin) && Hash::check($submittedSecret, $rawHashedPin)) {
                $pinValid = true;
            } elseif (strlen($masterCode) >= 6 && hash_equals($masterCode, $submittedSecret)) {
                $pinValid = true;
            }
        }

        if (! $user || ! $pinValid) {
            RateLimiter::hit($rateKey, 300);
            if ($verifier->isGoogleConfigured()) {
                $this->recaptchaToken = '';
                $this->dispatch('tokoku-recaptcha-reset');
            } else {
                $this->refreshSecurityChallenge();
            }

            throw ValidationException::withMessages([
                'recoveryPin' => 'Email atau PIN Pemulihan tidak sesuai untuk panel ini.',
            ]);
        }

        RateLimiter::clear($rateKey);
        $verifier->clearFallbackChallenge($this->captchaContextKey());

        $this->verifiedUserId = $user->id;
        $this->verifiedEmail = $user->email;
        $this->recoveryPin = '';
    }

    public function saveNewPassword(): ?PasswordResetResponse
    {
        if (! $this->verifiedUserId) {
            return null;
        }

        $this->validate([
            'newPassword' => ['required', 'string', PasswordRule::min(8), 'same:newPasswordConfirmation'],
            'newPasswordConfirmation' => ['required', 'string'],
        ], [
            'newPassword.required' => 'Kata sandi baru wajib diisi.',
            'newPassword.min' => 'Kata sandi baru minimal 8 karakter.',
            'newPassword.same' => 'Ulangan kata sandi baru belum sama.',
            'newPasswordConfirmation.required' => 'Ketik ulang kata sandi baru Anda.',
        ]);

        $panel = Filament::getCurrentPanel();
        $user = User::query()->find($this->verifiedUserId);

        if (! $user || ($panel && ! $user->canAccessPanel($panel))) {
            $this->cancelVerification();

            throw ValidationException::withMessages([
                'email' => 'Sesi pemulihan tidak valid. Silakan ulangi verifikasi PIN.',
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($this->newPassword),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $this->verifiedUserId = null;
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';

        Notification::make()
            ->title('Kata sandi berhasil diperbarui!')
            ->body('Silakan masuk menggunakan kata sandi baru Anda.')
            ->success()
            ->send();

        return app(PasswordResetResponse::class);
    }

    public function cancelVerification(): void
    {
        $this->verifiedUserId = null;
        $this->verifiedEmail = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
        $this->refreshSecurityChallenge();
    }

    protected function getViewData(): array
    {
        $verifier = app(RecaptchaVerifier::class);
        $panelId = Filament::getCurrentPanel()?->getId() ?? 'admin';
        $store = StoreSetting::current();

        return [
            'panelId' => $panelId,
            'loginUrl' => Filament::getLoginUrl(),
            'isGoogleRecaptcha' => $verifier->isGoogleConfigured(),
            'recaptchaSiteKey' => $verifier->siteKey(),
            'isLocalDemoAllowed' => app()->environment(['local', 'testing']),
            'adminHelpWaUrl' => $store->whatsappUrl('Halo Admin ' . $store->store_name . ', saya staf teknisi dan membutuhkan bantuan pengaturan ulang PIN / kata sandi akun meja teknisi.'),
        ];
    }

    private function captchaContextKey(): string
    {
        return 'reset_' . (Filament::getCurrentPanel()?->getId() ?? 'panel');
    }
}
