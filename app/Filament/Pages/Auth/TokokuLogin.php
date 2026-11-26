<?php

namespace App\Filament\Pages\Auth;

use App\Services\RecaptchaVerifier;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class TokokuLogin extends BaseLogin
{
    protected static string $layout = 'filament.layouts.auth-split';
    protected static string $view = 'filament.pages.auth.login';

    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public string $recaptchaToken = '';

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }
    }

    public function getTitle(): string | Htmlable
    {
        $panelId = Filament::getCurrentPanel()?->getId();

        return $panelId === 'admin'
            ? 'Login Panel Admin - Tokoku'
            : 'Login Meja Teknisi - Tokoku';
    }

    public function getHeading(): string | Htmlable
    {
        $panelId = Filament::getCurrentPanel()?->getId();

        return $panelId === 'admin'
            ? 'Masuk ke Panel Admin'
            : 'Masuk ke Meja Teknisi';
    }

    public function fillLocalDemoAccount(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $panelId = Filament::getCurrentPanel()?->getId();
        if ($panelId === 'admin') {
            $this->email = 'admin@tokoku.local';
            $this->password = 'admin123';
        } else {
            $this->email = 'teknisi@tokoku.local';
            $this->password = 'teknisi123';
        }
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email staf wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $verifier = app(RecaptchaVerifier::class);

        if ($verifier->isBlockedInProduction()) {
            throw ValidationException::withMessages([
                'recaptchaToken' => 'Kunci produksi Google reCAPTCHA belum dikonfigurasi pada server.',
            ]);
        }

        if (! $verifier->verifyToken($this->recaptchaToken, request()->ip())) {
            $this->resetRecaptchaWidget();

            throw ValidationException::withMessages([
                'recaptchaToken' => 'Centang kotak verifikasi "Saya bukan robot" (Google reCAPTCHA) terlebih dahulu.',
            ]);
        }

        $credentials = [
            'email' => trim($this->email),
            'password' => $this->password,
        ];

        if (! Filament::auth()->attempt($credentials, $this->remember)) {
            $this->resetRecaptchaWidget();

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai, atau akun belum terdaftar pada panel ini.',
            ]);
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();
            $this->resetRecaptchaWidget();

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai, atau akun tidak memiliki hak akses ke panel ini.',
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getViewData(): array
    {
        $verifier = app(RecaptchaVerifier::class);
        $panel = Filament::getCurrentPanel();

        return [
            'panelId' => $panel?->getId() ?? 'admin',
            'hasPasswordReset' => Filament::hasPasswordReset(),
            'passwordResetUrl' => Filament::hasPasswordReset() ? Filament::getRequestPasswordResetUrl() : null,
            'recaptchaConfigured' => $verifier->isConfigured(),
            'recaptchaSiteKey' => $verifier->siteKey(),
            'isUsingTestKey' => $verifier->isUsingGoogleTestKey(),
            'isLocalDemoAllowed' => app()->environment(['local', 'testing']),
        ];
    }

    private function resetRecaptchaWidget(): void
    {
        $this->recaptchaToken = '';
        $this->dispatch('tokoku-recaptcha-reset');
    }
}
