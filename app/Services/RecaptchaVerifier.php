<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class RecaptchaVerifier
{
    public const GOOGLE_PUBLIC_TEST_SITE_KEY = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';

    public function isConfigured(): bool
    {
        return filled(config('services.recaptcha.site_key'))
            && filled(config('services.recaptcha.secret_key'));
    }

    public function siteKey(): string
    {
        return trim((string) config('services.recaptcha.site_key', ''));
    }

    public function isUsingGoogleTestKey(): bool
    {
        return $this->siteKey() === self::GOOGLE_PUBLIC_TEST_SITE_KEY;
    }

    public function isBlockedInProduction(): bool
    {
        return app()->isProduction() && (! $this->isConfigured() || $this->isUsingGoogleTestKey());
    }

    public function verifyToken(?string $token, ?string $ip = null): bool
    {
        if ($this->isBlockedInProduction()) {
            return false;
        }

        $cleanToken = trim((string) $token);
        $secret = trim((string) config('services.recaptcha.secret_key', ''));

        if ($cleanToken === '' || $secret === '') {
            return false;
        }

        try {
            $payload = [
                'secret' => $secret,
                'response' => $cleanToken,
            ];
            if ($ip) {
                $payload['remoteip'] = $ip;
            }

            $response = Http::asForm()
                ->timeout(8)
                ->post('https://www.google.com/recaptcha/api/siteverify', $payload);

            return $response->successful() && (bool) $response->json('success', false);
        } catch (Throwable) {
            return false;
        }
    }
}
