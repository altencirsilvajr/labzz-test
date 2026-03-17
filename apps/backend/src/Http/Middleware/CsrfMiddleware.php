<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $csrfCookieName)
    {
    }

    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $route = $request->getAttribute('route_meta', []);
        $requiresCsrf = (bool) ($route['requires_csrf'] ?? false);

        if (!$requiresCsrf) {
            return $next($request);
        }

        $authorization = $request->getHeaderLine('authorization');
        if ($authorization !== '' && str_starts_with(strtolower($authorization), 'bearer ')) {
            return $next($request);
        }

        $cookieToken = $_COOKIE[$this->csrfCookieName] ?? '';
        $headerToken = $request->getHeaderLine('x-csrf-token');

        if ($cookieToken === '' || $headerToken === '' || !hash_equals($cookieToken, $headerToken)) {
            return Json::response(['error' => 'CSRF token mismatch.'], 419);
        }

        return $next($request);
    }
}
