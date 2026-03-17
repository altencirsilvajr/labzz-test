<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $response = $next($request);

        return $response
            ->withHeader('x-content-type-options', 'nosniff')
            ->withHeader('x-frame-options', 'DENY')
            ->withHeader('referrer-policy', 'strict-origin-when-cross-origin')
            ->withHeader('permissions-policy', 'camera=(), microphone=(), geolocation=()')
            ->withHeader('content-security-policy', "default-src 'self'; connect-src 'self' wss: https:; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-ancestors 'none'");
    }
}
