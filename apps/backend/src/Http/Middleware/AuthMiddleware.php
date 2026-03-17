<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Infrastructure\Security\Auth0JwtVerifier;
use App\Support\Json;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Auth0JwtVerifier $verifier)
    {
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $route = $request->getAttribute('route_meta', []);
        $requiresAuth = (bool) ($route['requires_auth'] ?? false);

        if (!$requiresAuth) {
            return $next($request);
        }

        try {
            $auth = $this->verifier->fromRequest($request);
        } catch (InvalidArgumentException $exception) {
            return Json::response(['error' => $exception->getMessage()], 401);
        }

        return $next($request->withAttribute('auth', $auth));
    }
}
