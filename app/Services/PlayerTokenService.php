<?php

namespace App\Services;

class PlayerTokenService
{
    protected string $secret;

    public function __construct()
    {
        $this->secret = env('PLAYER_TOKEN_SECRET', config('app.key')) ?: config('app.key');
    }

    public function generate(int $lessonId, int $userId, int $ttlSeconds = 300): string
    {
        $expires = time() + $ttlSeconds;
        $payload = json_encode(['lesson' => $lessonId, 'user' => $userId, 'exp' => $expires]);
        $sig = hash_hmac('sha256', $payload, $this->secret);
        $token = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=').'.'.$sig;

        return $token;
    }

    public function verify(string $token, int $lessonId, int $userId): bool
    {
        if (! str_contains($token, '.')) {
            return false;
        }
        [$b64, $sig] = explode('.', $token, 2);
        $payload = base64_decode(strtr($b64, '-_', '+/'));
        if (! $payload) {
            return false;
        }
        $data = json_decode($payload, true);
        if (! is_array($data)) {
            return false;
        }
        if (($data['lesson'] ?? null) !== $lessonId) {
            return false;
        }
        if (($data['user'] ?? null) !== $userId) {
            return false;
        }
        if (($data['exp'] ?? 0) < time()) {
            return false;
        }
        $expected = hash_hmac('sha256', $payload, $this->secret);

        return hash_equals($expected, $sig);
    }
}
