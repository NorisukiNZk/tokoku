<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class RecaptchaVerifier
{
    public function isGoogleConfigured(): bool
    {
        return filled(config('services.recaptcha.site_key'))
            && filled(config('services.recaptcha.secret_key'));
    }

    public function siteKey(): string
    {
        return (string) config('services.recaptcha.site_key', '');
    }

    public function generateFallbackChallenge(string $contextKey): string
    {
        $a = random_int(2, 9);
        $b = random_int(2, 9);

        session()->put($this->sessionKey($contextKey), $a + $b);

        return "Berapa hasil {$a} + {$b}?";
    }

    public function currentFallbackAnswerForLocalDemo(string $contextKey): ?int
    {
        $val = session($this->sessionKey($contextKey));

        return $val !== null ? (int) $val : null;
    }

    public function verifyFallbackAnswer(string $contextKey, mixed $answer): bool
    {
        $expected = session($this->sessionKey($contextKey));
        $cleaned = trim((string) $answer);

        if ($expected === null || $cleaned === '' || ! preg_match('/^-?\d+$/', $cleaned)) {
            return false;
        }

        return (int) $cleaned === (int) $expected;
    }

    public function clearFallbackChallenge(string $contextKey): void
    {
        session()->forget($this->sessionKey($contextKey));
    }

    public function verifyGoogleToken(?string $token, ?string $ip = null): bool
    {
        $cleanToken = trim((string) $token);
        $secret = (string) config('services.recaptcha.secret_key', '');

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

    private function sessionKey(string $contextKey): string
    {
        return 'tokoku.security_challenge.' . $contextKey;
    }
}
