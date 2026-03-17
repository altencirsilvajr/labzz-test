<?php

declare(strict_types=1);

namespace App\Modules\Realtime;

use App\Infrastructure\Security\AuthContext;
use Firebase\JWT\JWT;

final class WsTokenService
{
    public function __construct(private readonly string $appKey, private readonly int $ttlSeconds)
    {
    }

    public function issue(AuthContext $auth): string
    {
        $now = time();
        $payload = [
            'sub' => $auth->subject,
            'email' => $auth->email,
            'roles' => $auth->roles,
            'iat' => $now,
            'exp' => $now + $this->ttlSeconds,
            'type' => 'ws',
        ];

        return JWT::encode($payload, $this->secret(), 'HS256');
    }

    private function secret(): string
    {
        if (str_starts_with($this->appKey, 'base64:')) {
            $decoded = base64_decode(substr($this->appKey, 7), true);

            return $decoded !== false ? $decoded : $this->appKey;
        }

        return $this->appKey;
    }
}
