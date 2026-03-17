<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Json;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class JsonBodyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $contentType = strtolower($request->getHeaderLine('content-type'));
        if (!str_contains($contentType, 'application/json')) {
            return $next($request);
        }

        $raw = (string) $request->getBody();
        if ($raw === '') {
            return $next($request->withParsedBody([]));
        }

        $parsed = json_decode($raw, true);
        if (!is_array($parsed)) {
            return Json::response(['error' => 'Invalid JSON body.'], 400);
        }

        return $next($request->withParsedBody($parsed));
    }
}
