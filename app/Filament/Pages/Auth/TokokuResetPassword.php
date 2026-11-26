<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\PasswordResetResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\ResetPassword as BaseResetPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class TokokuResetPassword extends BaseResetPassword
{
    protected static string $layout = 'filament.layouts.auth-split';
    protected static string $view = 'filament.pages.auth.reset-password';

    public function mount(?string $email = null, ?string $token = null): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());

            return;
        }

        $this->token = $token ?? request()->query('token');
        $this->email = $email ?? request()->query('email');
    }

    public function getTitle(): string | Htmlable
    {
        return 'Buat Kata Sandi Baru - Tokoku';
    }

    public function getHeading(): string | Htmlable
    {
        return 'Buat Kata Sandi Baru';
    }

    public function resetPassword(): ?PasswordResetResponse
    {
        try {
            $this->rateLimit(3);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $this->validate([
            'password' => ['required', 'string', PasswordRule::min(8), 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.same' => 'Konfirmasi kata sandi tidak cocok.',
            'passwordConfirmation.required' => 'Ulangi kata sandi baru Anda.',
        ]);

        $credentials = [
            'email' => (string) $this->email,
            'password' => (string) $this->password,
            'password_confirmation' => (string) $this->passwordConfirmation,
            'token' => (string) $this->token,
        ];

        $hasPanelAccess = true;

        $status = Password::broker(Filament::getAuthPasswordBroker())->reset(
            $credentials,
            function (CanResetPassword | Model | Authenticatable $user) use ($credentials, &$hasPanelAccess) {
                if (
                    ($user instanceof FilamentUser) &&
                    (! $user->canAccessPanel(Filament::getCurrentPanel()))
                ) {
                    $hasPanelAccess = false;

                    return;
                }

                $user->forceFill([
                    'password' => Hash::make($credentials['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($hasPanelAccess === false) {
            $status = Password::INVALID_USER;
        }

        if ($status === Password::PASSWORD_RESET) {
            Notification::make()
                ->title('Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru.')
                ->success()
                ->send();

            return app(PasswordResetResponse::class);
        }

        Notification::make()
            ->title('Tautan pemulihan sudah kedaluwarsa atau tidak sesuai. Silakan ajukan tautan baru.')
            ->danger()
            ->send();

        return null;
    }

    protected function getViewData(): array
    {
        return [
            'loginUrl' => Filament::getLoginUrl(),
        ];
    }
}
