<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class Auth0JwtVerifier
{
    private CachedKeySet $keySet;

    /** @param array<string, mixed> $settings */
    public function __construct(private readonly array $settings)
    {
        if (empty($settings['domain']) || empty($settings['audience'])) {
            throw new InvalidArgumentException('Auth0 domain/audience must be configured.');
        }

        $jwksUri = sprintf('https://%s/.well-known/jwks.json', $settings['domain']);
        $httpClient = new Client();
        $psr17Factory = new Psr17Factory();
        $cachePool = new ArrayAdapter();

        $this->keySet = new CachedKeySet($jwksUri, $httpClient, $psr17Factory, $cachePool, 300, true);
    }

    public function fromRequest(RequestInterface $request): AuthContext
    {
        $header = $request->getHeaderLine('authorization');
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            throw new InvalidArgumentException('Missing bearer token.');
        }

        $decoded = JWT::decode($matches[1], $this->keySet);
        $claims = (array) $decoded;

        $aud = $claims['aud'] ?? null;
        $audience = is_array($aud) ? $aud : [$aud];
        if (!in_array($this->settings['audience'], $audience, true)) {
            throw new InvalidArgumentException('Invalid audience.');
        }

        return new AuthContext((string) ($claims['sub'] ?? ''), (string) ($claims['email'] ?? ''), []);
    }
}
