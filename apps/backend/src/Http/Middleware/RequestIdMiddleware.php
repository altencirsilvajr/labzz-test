<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

final class RequestIdMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $requestId = $request->getHeaderLine('x-request-id');
        if ($requestId === '') {
            $requestId = Uuid::uuid7()->toString();
        }

        $response = $next($request->withAttribute('request_id', $requestId));

        return $response->withHeader('x-request-id', $requestId);
    }
}
