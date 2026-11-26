<?php

namespace App\Filament\Pages\Auth;

use App\Services\RecaptchaVerifier;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Exception;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class TokokuRequestPasswordReset extends BaseRequestPasswordReset
{
    protected static string $layout = 'filament.layouts.auth-split';
    protected static string $view = 'filament.pages.auth.request-password-reset';

    public string $email = '';
    public string $recaptchaToken = '';
    public string $securityAnswer = '';
    public string $challengePrompt = '';
    public bool $resetRequested = false;

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
        return 'Pulihkan Kata Sandi Akun';
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

    public function request(): void
    {
        try {
            $this->rateLimit(3);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $this->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Masukkan alamat email akun staf Anda.',
            'email.email' => 'Format alamat email tidak valid.',
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

        Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
            ['email' => trim($this->email)],
            function (CanResetPassword $user, string $token): void {
                if (
                    ($user instanceof FilamentUser) &&
                    (! $user->canAccessPanel(Filament::getCurrentPanel()))
                ) {
                    return;
                }

                if (! method_exists($user, 'notify')) {
                    $userClass = $user::class;

                    throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
                }

                $notification = app(ResetPasswordNotification::class, ['token' => $token]);
                $notification->url = Filament::getResetPasswordUrl($token, $user);

                $user->notify($notification);

                if (class_exists(PasswordResetLinkSent::class)) {
                    event(new PasswordResetLinkSent($user));
                }
            },
        );

        $this->resetRequested = true;
        $this->email = '';
        $this->refreshSecurityChallenge();

        Notification::make()
            ->title('Permintaan pemulihan kata sandi diproses.')
            ->body('Jika email terdaftar pada panel ini, tautan atur ulang kata sandi telah dikirimkan.')
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $verifier = app(RecaptchaVerifier::class);

        return [
            'loginUrl' => Filament::getLoginUrl(),
            'isGoogleRecaptcha' => $verifier->isGoogleConfigured(),
            'recaptchaSiteKey' => $verifier->siteKey(),
            'isLogMailer' => config('mail.default') === 'log',
        ];
    }

    private function captchaContextKey(): string
    {
        return 'reset_' . (Filament::getCurrentPanel()?->getId() ?? 'panel');
    }
}
